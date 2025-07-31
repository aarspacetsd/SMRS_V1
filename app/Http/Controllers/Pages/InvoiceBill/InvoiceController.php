<?php

namespace App\Http\Controllers\Pages\InvoiceBill;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Auth; // <-- Tambahkan ini
class InvoiceController extends Controller
{
  // public function __construct()
  // {
  //   $this->middleware('auth');
  // }

  // /**
  //  * Menampilkan halaman laporan semua faktur dengan filter.
  //  */
  // public function index(Request $request)
  // {
  //   // Validasi input dari form filter
  //   $request->validate([
  //     'user_id' => 'nullable|integer|exists:users,id',
  //     'from_date' => 'nullable|date',
  //     'to_date' => 'nullable|date|after_or_equal:from_date',
  //   ]);

  //   // Query dasar untuk mengambil data faktur beserta relasi user
  //   $query = Invoice::with('user');

  //   // Terapkan filter jika ada
  //   if ($request->filled('user_id')) {
  //     $query->where('user_id', $request->user_id);
  //   }

  //   if ($request->filled('from_date')) {
  //     $query->whereDate('created_at', '>=', $request->from_date);
  //   }

  //   if ($request->filled('to_date')) {
  //     $query->whereDate('created_at', '<=', $request->to_date);
  //   }

  //   // Eksekusi query dan ambil hasilnya, diurutkan berdasarkan yang terbaru
  //   $invoices = $query->latest()->get();

  //   // Mengambil semua user untuk ditampilkan di dropdown filter
  //   $users = User::orderBy('name')->get();

  //   // Mengirim data ke view
  //   return view('content.pages.invoice.allreport', compact('invoices', 'users'));
  // }
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan halaman laporan semua faktur dengan filter.
   */
  public function index(Request $request)
  {
    // Validasi input dari form filter
    $request->validate([
      'user_id' => 'nullable|integer|exists:users,id',
      'from_date' => 'nullable|date',
      'to_date' => 'nullable|date|after_or_equal:from_date',
    ]);

    // --- PERBAIKAN: Menambahkan 'user.roles' ke eager loading ---
    $query = Invoice::with('user.roles');

    // Terapkan filter jika ada
    if ($request->filled('user_id')) {
      $query->where('user_id', $request->user_id);
    }
    if ($request->filled('from_date')) {
      $query->whereDate('created_at', '>=', $request->from_date);
    }
    if ($request->filled('to_date')) {
      $query->whereDate('created_at', '<=', $request->to_date);
    }

    $invoices = $query->latest()->get();
    $users = User::orderBy('name')->get();

    // Mengambil role dari user yang sedang login
    $role = Auth::user() ? Auth::user()->getRoleNames()->first() : 'Guest';

    // Mengirim data ke view, termasuk variabel $role
    // Mengirim data ke view, termasuk variabel $role
    return view('content.pages.invoice.allreport', compact('invoices', 'users', 'role'));
  }
}
