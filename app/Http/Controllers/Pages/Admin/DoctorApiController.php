<?php


namespace App\Http\Controllers\Pages\Admin; // <-- PERBAIKAN: Namespace disesuaikan

use App\Http\Controllers\Controller; // <-- PERBAIKAN: Menambahkan use statement untuk base Controller
use Illuminate\Http\Request;
use App\Models\Doctor;
use Exception;

class DoctorApiController extends Controller
{
  /**
   * Mengambil jadwal dokter berdasarkan ID dan mengembalikannya sebagai JSON.
   * Direvisi dengan try-catch untuk debugging error 500.
   */
  public function getSchedule($doctor_id)
  {
    try {
      // Menggunakan findOrFail untuk otomatis menangani jika dokter tidak ditemukan
      // Ini akan melempar ModelNotFoundException jika ID tidak ada, yang akan ditangkap oleh blok catch.
      $doctor = Doctor::with('employee')->findOrFail($doctor_id);

      // Cek jika relasi employee atau data jadwal ada
      // Ini adalah pengecekan penting untuk menghindari error "Trying to get property of non-object"
      if (!$doctor->employee) {
        // Jika relasi employee tidak ada, kirim response bahwa jadwal tidak tersedia
        return response()->json(['error' => 'Data karyawan untuk dokter ini tidak ditemukan.'], 404);
      }

      if (!$doctor->employee->working_day) {
        // Jika kolom working_day kosong
        return response()->json(['error' => 'Dokter ini belum memiliki jadwal kerja (working_day).'], 404);
      }

      // Memecah hari kerja menjadi array
      $days = explode(',', $doctor->employee->working_day);

      // Mengambil rentang waktu kerja
      $available_time = $doctor->employee->in_time . ' - ' . $doctor->employee->out_time;

      $scheduleList = [];
      foreach ($days as $day) {
        // Menambahkan setiap jadwal ke dalam array
        // trim() untuk menghapus spasi yang tidak diinginkan
        $scheduleList[] = trim($day) . ' (' . $available_time . ')';
      }

      // Mengembalikan data dalam format JSON yang diharapkan oleh frontend
      return response()->json([
        'schedule' => $scheduleList
      ]);
    } catch (Exception $e) {
      // Jika terjadi error APAPUN di dalam blok try, tangkap di sini.
      // Kirim response error 500 dengan pesan error yang sebenarnya.
      return response()->json([
        'error' => 'Terjadi kesalahan pada server.',
        'message' => $e->getMessage(), // Pesan error PHP yang spesifik
        'file' => $e->getFile(),       // File tempat error terjadi
        'line' => $e->getLine()        // Baris tempat error terjadi
      ], 500);
    }
  }
}
