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
    $user = Auth::user();
    if (!$user) {
      // Sebaiknya redirect atau abort, bukan dd() di production
      return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
    }

    // DEFINISIKAN VARIABEL ROLE DAN NAMA
    $role = $user->getRoleNames()->first() ?? 'Guest';
    $name = $user->name;

    $today = today();

    // --- PERBAIKAN: Gunakan Eager Loading dengan with() ---
    $invoicesToday = Invoice::where('user_id', $user->id)->whereDate('created_at', $today)->get();

    // Memuat relasi patient dan doctor.employee sekaligus
    $appointmentsToday = Appointment::with(['patient', 'doctor.employee'])
      ->whereDate('appointment_date', $today)
      ->get();

    // Memuat relasi patient dan doctor.employee sekaligus
    $opdsToday = OpdSales::with(['patient', 'doctor.employee'])
      ->whereDate('created_at', $today)
      ->get();
    // dd($opdsToday->toArray());

    // Kalkulasi total invoice
    $total = [
      'sub_total'    => $invoicesToday->sum('sub_total'),
      'discount'     => $invoicesToday->sum('discount'),
      'tax_amount'   => $invoicesToday->sum('tax_amount'),
      'total_amount' => $invoicesToday->sum('total_amount'),
    ];

    // Hitung total data master
    $total_patients = Patient::count();
    $total_doctors = Doctor::count();
    $total_tests = Test::count();
    $pending_appointments = Appointment::where('status', 0)->count();


    // KIRIM SEMUA VARIABEL KE VIEW
    return view('dashboard', [
      'role' => $role,
      'name' => $name,
      'invoices' => $invoicesToday,
      'total' => $total,
      'appointments' => $appointmentsToday, // Sekarang sudah berisi data relasi
      'opds' => $opdsToday,                 // Sekarang sudah berisi data relasi
      'total_patients' => $total_patients,
      'total_doctors' => $total_doctors,
      'total_tests' => $total_tests,
      'pending_appointments' => $pending_appointments,
    ]);
  }
}
