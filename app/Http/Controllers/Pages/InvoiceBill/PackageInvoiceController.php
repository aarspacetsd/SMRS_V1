<?php

namespace App\Http\Controllers\Pages\InvoiceBill;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\PackageSale;
use App\Models\Hospital;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PackageInvoiceController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan halaman form untuk membuat faktur paket.
   */
  public function create()
  {
    $packages = Package::orderBy('name')->get();
    $patients = Patient::orderBy('first_name')->get();

    // Membuat nomor faktur baru dengan aman
    $lastInvoice = Invoice::latest('id')->first();
    $invoice_no = $lastInvoice ? $lastInvoice->id + 1 : 1;

    return view('content.pages.invoice.packageinvoice', compact('packages', 'patients', 'invoice_no'));
  }

  /**
   * Menyimpan faktur paket baru dan detail penjualannya.
   */
  public function store(Request $request)
  {
    $request->validate([
      'package_id' => 'required|exists:packages,id',
      'patient_id' => 'required|exists:patients,id',
      'invoice_no' => 'required|unique:invoices,invoice_no',
      'cash' => 'nullable|numeric|min:0',
      'discount' => 'nullable|numeric|min:0',
      'payment_type' => 'required|string',
    ]);

    $package = Package::findOrFail($request->package_id);
    $hospital = Hospital::firstOrFail();
    $tax_percent = $hospital->tax_percent;

    // --- Perhitungan ---
    $sub_total = $package->price;
    $discount = $request->discount ?? 0;
    $sub_total_after_discount = $sub_total - $discount;
    $tax_amount = $sub_total_after_discount * $tax_percent / 100;
    $total_amount = $sub_total_after_discount + $tax_amount;

    // --- Transaksi Database ---
    DB::transaction(function () use ($request, $package, $sub_total, $discount, $tax_amount, $total_amount) {
      // 1. Buat Invoice Utama
      $invoice = Invoice::create([
        'sub_total' => $sub_total,
        'discount' => $discount,
        'tax_amount' => $tax_amount,
        'total_amount' => $total_amount,
        'patient_id' => $request->patient_id,
        'invoice_no' => $request->invoice_no,
        'comment' => $request->comment,
        'payment_type' => $request->payment_type,
        'user_id' => Auth::id(),
        'cash' => $request->cash ?? 0,
      ]);

      // 2. Simpan catatan penjualan paket
      PackageSale::create([
        'invoice_id' => $invoice->id,
        'package_id' => $package->id,
        'patient_id' => $request->patient_id,
        'package_price' => $package->price,
      ]);

      // Anda bisa menambahkan logika untuk membuat Report di sini jika perlu
    });

    return redirect()->route('package-invoice.create')->with('success', 'Faktur Paket berhasil dibuat.');
  }

  /**
   * API untuk mengambil detail paket (harga, pajak, dll).
   */
  public function getPackageDetails(Package $package)
  {
    $hospital = Hospital::first();
    $tax_percent = $hospital->tax_percent ?? 0;

    $tax_amount = $package->price * $tax_percent / 100;
    $total_amount = $package->price + $tax_amount;

    return response()->json([
      'name' => $package->name,
      'price' => $package->price,
      'tax_percent' => $tax_percent,
      'tax_amount' => $tax_amount,
      'total_amount' => $total_amount,
    ]);
  }
}
