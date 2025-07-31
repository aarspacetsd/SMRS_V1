<?php

namespace App\Http\Controllers\Pages\Report;

use App\Http\Controllers\Controller; // Pastikan namespace ini sesuai dengan lokasi file Anda

use App\Models\Package;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
  /**
   * Menampilkan daftar semua paket beserta tes yang termasuk.
   */
  public function index()
  {
    // Eager load relasi 'tests' untuk efisiensi query
    $packages = Package::with('tests')->get();
    // --- PERBAIKAN ---
    // Menghapus filter where('status', 1) karena kolom 'status' tidak ada di tabel 'tests'
    $tests = Test::get();
    return view('content.pages.report.packagereport', compact('packages', 'tests'));
  }

  /**
   * Menyimpan paket baru ke dalam database.
   */
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255|unique:packages,name',
      'price' => 'required|numeric|min:0',
      'description' => 'nullable|string',
      'test_ids' => 'required|array',
      'test_ids.*' => 'exists:tests,id', // Memastikan semua ID tes valid
    ]);

    // Menggunakan transaksi database untuk memastikan integritas data
    DB::transaction(function () use ($request) {
      $package = Package::create([
        'name' => $request->name,
        'description' => $request->description,
        'price' => $request->price,
      ]);

      // Menyinkronkan tes yang dipilih dengan paket menggunakan relasi many-to-many
      $package->tests()->sync($request->test_ids);
    });

    return back()->with('success', 'Paket berhasil dibuat.');
  }

  /**
   * Memperbarui data paket yang ada.
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

      // Sync akan secara otomatis menambah, menghapus, atau membiarkan tes yang ada
      $package->tests()->sync($request->test_ids);
    });

    return back()->with('success', 'Paket berhasil diperbarui.');
  }

  /**
   * Menghapus paket dari database.
   */
  public function destroy(Package $package)
  {
    // Cek apakah paket pernah terjual sebelum menghapus
    if ($package->packageSales()->exists()) {
      return back()->with('error', 'Paket tidak dapat dihapus karena memiliki riwayat penjualan.');
    }

    $package->delete(); // Ini juga akan menghapus relasi di tabel package_tests
    return back()->with('success', 'Paket berhasil dihapus.');
  }
}
