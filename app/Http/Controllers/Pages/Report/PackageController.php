<?php

// Namespace ini harus sesuai dengan lokasi file Anda.
// Jika controller ini untuk manajemen paket, namespace 'Admin' atau 'Package' lebih sesuai daripada 'Report'.
namespace App\Http\Controllers\Pages\Report;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan daftar semua paket beserta tes yang termasuk.
   */
  public function index()
  {
    // Eager loading (with('tests')) adalah cara paling efisien untuk mengambil data
    // beserta relasinya untuk menghindari masalah N+1 query.
    $packages = Package::with('tests')->get();

    // Mengambil semua data tes untuk ditampilkan di form tambah/edit.
    $tests = Test::get();

    // Mengirim data ke view. Pastikan path view ini benar.
    return view('content.pages.report.packagereport', compact('packages', 'tests'));
  }

  /**
   * Menyimpan paket baru ke dalam database.
   */
  public function store(Request $request)
  {
    // Validasi yang kuat memastikan data yang masuk bersih dan sesuai.
    $request->validate([
      'name' => 'required|string|max:255|unique:packages,name',
      'price' => 'required|numeric|min:0',
      'description' => 'nullable|string',
      'test_ids' => 'required|array',
      'test_ids.*' => 'exists:tests,id', // Memastikan semua ID tes yang dikirim valid.
    ]);

    // Menggunakan transaksi database untuk menjaga integritas data.
    // Jika salah satu proses gagal, semua akan dibatalkan.
    DB::transaction(function () use ($request) {
      $package = Package::create([
        'name' => $request->name,
        'description' => $request->description,
        'price' => $request->price,
      ]);

      // sync() adalah cara terbaik untuk mengelola relasi many-to-many.
      // Ini akan secara otomatis menambah/menghapus relasi di tabel pivot.
      $package->tests()->sync($request->test_ids);
    });

    return back()->with('success', 'Paket berhasil dibuat.');
  }

  /**
   * Memperbarui data paket yang ada.
   * Menggunakan Route Model Binding (Package $package) untuk kode yang lebih bersih.
   */
  public function update(Request $request, Package $package)
  {
    $request->validate([
      'name' => 'required|string|max:255|unique:packages,name,' . $package->id,
      'price' => 'required|numeric|min:0',
      'description' => 'nullable|string',
      'test_ids' => 'required|array',
      'test_ids.*' => 'exists:tests,id',
    ]);

    DB::transaction(function () use ($request, $package) {
      $package->update([
        'name' => $request->name,
        'description' => $request->description,
        'price' => $request->price,
      ]);

      $package->tests()->sync($request->test_ids);
    });

    return back()->with('success', 'Paket berhasil diperbarui.');
  }

  /**
   * Menghapus paket dari database.
   */
  public function destroy(Package $package)
  {
    // Pengecekan relasi sebelum menghapus adalah praktik yang aman.
    if ($package->packageSales()->exists()) {
      return back()->with('error', 'Paket tidak dapat dihapus karena memiliki riwayat penjualan.');
    }

    // Saat $package dihapus, entri terkait di tabel pivot 'package_test'
    // akan otomatis terhapus jika Anda menggunakan onDelete('cascade') di migrasi.
    $package->delete();

    return back()->with('success', 'Paket berhasil dihapus.');
  }
}
