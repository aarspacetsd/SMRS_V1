<?php

namespace App\Http\Controllers\Pages\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hospital;
// use App\Models\Tax; // Hapus atau komentari baris ini jika Anda tidak lagi menggunakan model Tax
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Exception;

class HospitalSettingController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan halaman pengaturan utama.
   */
  public function setting()
  {
    // Mengambil data rumah sakit pertama
    $hospital = Hospital::first();

    // Jika tidak ada data rumah sakit sama sekali, inisialisasi objek Hospital kosong
    // agar view tidak error saat mencoba mengakses propertinya.
    if (!$hospital) {
      $hospital = new Hospital([
        'name' => 'Nama Rumah Sakit Anda',
        'slogan' => '',
        'address' => '',
        'contact' => '',
        'email' => '',
        'pan_no' => '',
        'registration_no' => '',
        'website' => '',
        'description' => '',
        'tax_type' => 'Default Tax', // Inisialisasi default untuk pajak
        'tax_percent' => 0,          // Inisialisasi default untuk pajak
        'invoice_prefix' => '',
        'patient_prefix' => '',
        'invoice_message' => '',
      ]);
      // Catatan: Jika Anda ingin ini tersimpan di DB, Anda harus $hospital->save();
      // Tapi untuk tampilan awal, cukup inisialisasi objek.
    }

    // Sekarang, $hospital sudah berisi semua data, termasuk tax_type dan tax_percent.
    // Kita hanya perlu mengirim $hospital ke view.
    return view('content.pages.settings.hospitalsettings', compact('hospital'));
  }


  /**
   * Memperbarui informasi rumah sakit.
   */
  public function updateHospital(Request $request, $id)
  {
    $hospital = Hospital::find($id);
    if (!$hospital) return back()->with('error', 'Data rumah sakit tidak ditemukan.');

    $this->validate($request, [
      'name' => 'required|string|max:255',
      'slogan' => 'required|string|max:255',
      'address' => 'required|string',
      'contact' => 'required|string',
      'email' => 'required|email',
    ]);

    $hospital->update($request->all());
    return back()->with('success', 'Pengaturan Rumah Sakit berhasil diperbarui.');
  }

  /**
   * Memperbarui pengaturan pajak.
   */
  public function updateTax(Request $request, $id)
  {
    $tax = Tax::find($id);
    if (!$tax) return back()->with('error', 'Data pajak tidak ditemukan.');

    $this->validate($request, [
      'name' => 'required|string|max:255',
      'percent' => 'required|numeric|min:0',
    ]);

    $tax->update($request->all());
    return back()->with('success', 'Pengaturan Pajak berhasil diperbarui.');
  }

  /**
   * Memperbarui konfigurasi aplikasi.
   */
  public function updateConfig(Request $request)
  {
    // Logika untuk menyimpan ke file config (memerlukan pendekatan yang lebih kompleks)
    // Untuk saat ini, kita akan fokus pada fungsionalitas lain.
    // Disarankan untuk menyimpan prefix di database agar lebih mudah dikelola.
    return back()->with('info', 'Fungsi update konfigurasi sedang dalam pengembangan.');
  }

  /**
   * Melakukan backup database.
   */
  public function backup()
  {
    try {
      $db_name = env('DB_DATABASE');
      $db_user = env('DB_USERNAME');
      $db_password = env('DB_PASSWORD');
      $db_host = env('DB_HOST');
      $backup_path = storage_path('app/backups/' . date('Y-m-d-H-i-s') . '.sql');

      // Membuat direktori jika belum ada
      if (!is_dir(dirname($backup_path))) {
        mkdir(dirname($backup_path), 0755, true);
      }

      $command = sprintf(
        'mysqldump --host=%s --user=%s --password=%s %s > %s',
        escapeshellarg($db_host),
        escapeshellarg($db_user),
        escapeshellarg($db_password),
        escapeshellarg($db_name),
        escapeshellarg($backup_path)
      );

      exec($command, $output, $worked);

      if ($worked === 0) {
        return back()->with('success', 'Database berhasil dicadangkan.');
      } else {
        return back()->with('error', 'Gagal membuat cadangan database. Periksa konfigurasi .env Anda.');
      }
    } catch (\Exception $e) {
      return back()->with('error', 'Terjadi error: ' . $e->getMessage());
    }
  }
}
