<?php

// Sesuaikan namespace dengan struktur folder proyek Anda
namespace App\Http\Controllers\Pages\LabTest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\TestReport;
use App\Models\Hospital;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use PDF; // Pastikan Anda sudah menginstal library PDF, contoh: barryvdh/laravel-dompdf

class ReportController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan halaman daftar laporan.
   */
  public function index()
  {
    // Mengambil laporan beserta relasi pasien (eager loading) untuk efisiensi
    $reports = Report::with('patient')->latest()->get();
    return view('content.pages.labtest.reportlab', compact('reports'));
  }

  /**
   * Menampilkan halaman untuk mengedit (misal: sample collect).
   * Logika ini bisa Anda kembangkan lebih lanjut.
   */
  public function edit(Report $report)
  {
    // Anda bisa membuat view terpisah untuk halaman edit ini
    // Untuk saat ini, kita bisa dd() untuk memastikan data benar
    // dd($report->load('test_reports.test'));
    return back()->with('info', 'Halaman edit untuk laporan #' . $report->id . ' belum diimplementasikan sepenuhnya.');
  }

  /**
   * Memproses pembaruan laporan.
   */
  public function update(Request $request, Report $report)
  {
    // Logika untuk update 'sample collect' dari controller referensi
    if ($request->has('sample')) {
      foreach ($request->sample as $testReportId) {
        TestReport::where('id', $testReportId)
          ->where('report_id', $report->id)
          ->update(['sample' => 1]);
      }
      return back()->with('success', 'Sample berhasil dikumpulkan.');
    }
    return back()->with('error', 'Tidak ada aksi yang dilakukan.');
  }

  /**
   * Menghasilkan dan menampilkan laporan dalam format PDF.
   * Logika ini disederhanakan dari controller referensi Anda.
   */
  public function generatePdf(Report $report)
  {
    // Di sini Anda bisa menempatkan semua logika kompleks dari method 'printReport'
    // dari controller referensi Anda untuk menghasilkan HTML.
    // Untuk contoh, kita buat PDF sederhana.

    $setting = Hospital::first();
    $html = "<h1>Laporan untuk Pasien: {$report->patient->first_name}</h1>";
    $html .= "<p>No. Laporan: {$report->id}</p>";
    // Tambahkan logika loop untuk test_reports di sini...

    $pdf = PDF::loadHtml($html);
    return $pdf->stream('laporan-' . $report->id . '.pdf');
  }
}
