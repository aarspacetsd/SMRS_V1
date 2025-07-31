<?php

// PASTIKAN NAMESPACE INI SESUAI DENGAN LOKASI FILE ANDA
namespace App\Http\Controllers\Pages\InvoiceBill;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\Hospital;
use App\Models\OpdSales;
use Illuminate\Support\Facades\Auth;

class OpdInvoiceController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan halaman laporan penjualan OPD.
   * Sesuai dengan rute: /dashboard/reports/opd
   *
   * @return \Illuminate\Http\Response
   */
  public function report()
  {
    // Mengambil semua data penjualan OPD dengan relasi yang dibutuhkan.
    $opd_sales = OpdSales::with(['invoice.user', 'doctor.employee', 'patient']) // Tambahkan patient
      ->latest('id')
      ->get();

    // Mengirim data ke view laporan.
    return view('content.pages.report.opdreport', compact('opd_sales'));
  }


  /**
   * Menampilkan halaman form untuk membuat faktur OPD baru.
   * Sesuai dengan rute: /dashboard/reports/opd-invoice/create
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $doctors = Doctor::with('employee')->get();
    $patients = Patient::get();

    // Membuat nomor faktur baru dengan aman.
    $lastInvoice = Invoice::latest('id')->first();
    $invoice_no = $lastInvoice ? $lastInvoice->id + 1 : 1;

    // Mengirim data ke view form pembuatan faktur.
    return view('content.pages.invoice.opdinvoice', compact('doctors', 'patients', 'invoice_no'));
  }

  /**
   * Menyimpan faktur OPD baru dan data penjualannya.
   * Sesuai dengan rute: POST /dashboard/reports/opd-invoice
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $this->validate($request, [
      'doctor_id' => 'required|exists:doctors,id',
      'patient_id' => 'required|exists:patients,id',
      'invoice_no' => 'required|unique:invoices,invoice_no',
      'cash' => 'nullable|numeric|min:0',
      'discount' => 'nullable|numeric|min:0',
      'payment_type' => 'required|string',
    ]);

    $doctor = Doctor::find($request->doctor_id);
    $hospital = Hospital::first();

    // Mencegah error jika data rumah sakit tidak ada.
    if (!$hospital) {
      return back()->with('error', 'Pengaturan rumah sakit tidak ditemukan.')->withInput();
    }

    // --- Perhitungan ---
    $tax_percent = $hospital->tax_percent;
    $sub_total = $doctor->opd_charge;
    $discount = $request->discount ?? 0;

    $sub_total_after_discount = $sub_total - $discount;
    $tax_amount = $sub_total_after_discount * $tax_percent / 100;
    $total_amount = $sub_total_after_discount + $tax_amount;

    // --- Membuat Invoice ---
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

    // --- Membuat Catatan Penjualan OPD ---
    OpdSales::create([
      'doctor_id' => $request->doctor_id,
      'patient_id' => $request->patient_id, // <-- PERBAIKAN: Menambahkan patient_id
      'invoice_id' => $invoice->id,
      'doctor_fee' => $doctor->fee,
      'opd_charge' => $doctor->opd_charge,
      'opd_name' => 'OPD Charge(' . $doctor->employee->first_name . ' ' . $doctor->employee->last_name . ')',
    ]);

    // Mengarahkan kembali ke halaman laporan dengan pesan sukses.
    return redirect()->route('invoice-bill.opd.create')->with('success', 'Faktur OPD berhasil dibuat.');
  }

  /**
   * Mengambil detail biaya OPD untuk dokter tertentu (untuk AJAX).
   * Sesuai dengan rute: /dashboard/reports/api/opd-sales/{id}
   *
   * @param  int  $doctor_id
   * @return \Illuminate\Http\Response
   */
  public function opdSales($doctor_id)
  {
    $doctor = Doctor::with('employee')->find($doctor_id);
    $hospital = Hospital::first();

    if (!$doctor || !$hospital) {
      return response()->json(['error' => 'Data dokter atau rumah sakit tidak ditemukan.'], 404);
    }

    $data = [
      'doctor_name' => $doctor->employee->first_name . ' ' . $doctor->employee->last_name,
      'opd_charge' => $doctor->opd_charge,
      'tax_percent' => $hospital->tax_percent,
    ];

    return response()->json($data);
  }
}
