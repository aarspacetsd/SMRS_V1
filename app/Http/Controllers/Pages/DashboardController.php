<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

// Import Models
use App\Models\Invoice;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\OpdSales;
use App\Models\Test;
use App\Models\Doctor;

class DashboardController extends Controller
{
  /**
   * Menerapkan middleware auth untuk seluruh method di controller ini.
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan halaman dashboard.
   */
  public function index()
  {
    // Dapatkan ID user yang sedang login dan tanggal hari ini.
    // Menggunakan helper today() lebih direkomendasikan.
    $userId = Auth::id(); // Auth::id() adalah shortcut untuk Auth::user()->id
    $today = today();

    // Ambil data invoice HARI INI untuk user yang login
    $invoicesToday = Invoice::where('user_id', $userId)->whereDate('created_at', $today)->get();

    // Ambil data appointments & OPD Sales untuk HARI INI
    $appointmentsToday = Appointment::whereDate('appointment_date', $today)->get();
    $opdsToday = OpdSales::whereDate('created_at', $today)->get();

    // Hitung total dari koleksi invoice (efisien)
    $total = [
      'sub_total'    => $invoicesToday->sum('sub_total'),
      'discount'     => $invoicesToday->sum('discount'),
      'tax_amount'   => $invoicesToday->sum('tax_amount'),
      'total_amount' => $invoicesToday->sum('total_amount'),
    ];

    // Hitung total data master (efisien menggunakan count())
    $total_patients = Patient::count();
    $total_doctors = Doctor::count();
    $total_tests = Test::count();

    // Hitung data pending
    // Asumsi: menghitung SEMUA appointment yang pending, tidak hanya hari ini.
    $pending_appointments = Appointment::where('status', 0)->count();


    return view('dashboard', [
      'invoices' => $invoicesToday,
      'total' => $total,
      'appointments' => $appointmentsToday,
      'opds' => $opdsToday,
      'total_patients' => $total_patients,
      'total_doctors' => $total_doctors,
      'total_tests' => $total_tests,
      'pending_appointments' => $pending_appointments,
    ]);
  }
}
