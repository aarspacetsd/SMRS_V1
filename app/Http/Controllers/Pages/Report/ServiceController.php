<?php

namespace App\Http\Controllers\Pages\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceSale; // Pastikan Anda memiliki model ini
use App\Models\Service;

class ServiceController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan halaman laporan penjualan layanan dengan filter.
   */
  public function index(Request $request)
  {
    // Validasi input filter (opsional tapi direkomendasikan)
    $request->validate([
      'service_id' => 'nullable|integer|exists:services,id',
      'from_date' => 'nullable|date',
      'to_date' => 'nullable|date|after_or_equal:from_date',
    ]);

    // Query dasar untuk mengambil data penjualan layanan beserta relasinya
    // Eager loading untuk performa yang lebih baik
    $query = ServiceSale::with(['invoice.user', 'service']);

    // Filter berdasarkan layanan yang dipilih
    if ($request->filled('service_id')) {
      $query->where('service_id', $request->service_id);
    }

    // Filter berdasarkan rentang tanggal (From Date)
    if ($request->filled('from_date')) {
      $query->whereDate('created_at', '>=', $request->from_date);
    }

    // Filter berdasarkan rentang tanggal (To Date)
    if ($request->filled('to_date')) {
      $query->whereDate('created_at', '<=', $request->to_date);
    }

    // Eksekusi query dan ambil hasilnya, diurutkan berdasarkan yang terbaru
    $service_sales = $query->latest()->get();

    // Mengambil semua layanan untuk ditampilkan di dropdown filter
    $services = Service::orderBy('name')->get();

    // Mengirim data ke view
    return view('content.pages.report.servicereport', compact('service_sales', 'services'));
  }
}
