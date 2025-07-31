<?php

namespace App\Http\Controllers\Pages\InvoiceBill;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\ServiceSale;
use App\Models\Hospital;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ServiceBillController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan halaman form untuk membuat Service Bill.
   */
  public function create()
  {
    // Selalu bersihkan keranjang lama saat halaman dimuat
    Session::forget('service_bill_cart');

    $services = Service::orderBy('name')->get();
    $patients = Patient::orderBy('first_name')->get();

    // Membuat nomor faktur baru dengan aman
    $lastInvoice = Invoice::latest('id')->first();
    $invoice_no = $lastInvoice ? $lastInvoice->id + 1 : 1;

    return view('content.pages.invoice.servicebill', compact('services', 'patients', 'invoice_no'));
  }

  /**
   * Menyimpan Service Bill baru ke database.
   */
  public function store(Request $request)
  {
    $request->validate([
      'patient_id' => 'required|exists:patients,id',
      'invoice_no' => 'required|unique:invoices,invoice_no',
      'payment_type' => 'required|string',
      'discount' => 'nullable|numeric|min:0',
      'cash' => 'nullable|numeric|min:0',
    ]);

    $cart = Session::get('service_bill_cart', []);
    if (empty($cart)) {
      return back()->with('error', 'Keranjang layanan kosong.')->withInput();
    }

    $hospital = Hospital::firstOrFail();
    $tax_percent = $hospital->tax_percent;

    // --- Perhitungan ---
    $sub_total = collect($cart)->sum('amount');
    $discount = $request->discount ?? 0;
    $sub_total_after_discount = $sub_total - $discount;
    $tax_amount = $sub_total_after_discount * $tax_percent / 100;
    $total_amount = $sub_total_after_discount + $tax_amount;

    // --- Transaksi Database ---
    DB::transaction(function () use ($request, $cart, $sub_total, $discount, $tax_amount, $total_amount) {
      $invoice = Invoice::create([
        'sub_total' => $sub_total,
        'discount' => $discount,
        'tax_amount' => $tax_amount,
        'total_amount' => $total_amount,
        'patient_id' => $request->patient_id,
        'invoice_no' => $request->invoice_no,
        'payment_type' => $request->payment_type,
        'user_id' => Auth::id(),
        'cash' => $request->cash ?? 0,
      ]);

      foreach ($cart as $item) {
        ServiceSale::create([
          'invoice_id' => $invoice->id,
          'service_id' => $item['id'],
          'service_name' => $item['name'],
          'amount' => $item['amount'],
        ]);
      }
    });

    Session::forget('service_bill_cart');
    return redirect()->route('invoice-bill.service-bill.create')->with('success', 'Service Bill berhasil dibuat.');
  }

  // --- API Methods untuk Keranjang Belanja ---

  public function addToCart(Request $request)
  {
    $request->validate(['service_id' => 'required|exists:services,id']);
    $service = Service::find($request->service_id);
    $cart = Session::get('service_bill_cart', []);

    if (isset($cart[$service->id])) {
      return response()->json(['error' => 'Layanan sudah ada di keranjang.'], 422);
    }

    $cart[$service->id] = ['id' => $service->id, 'name' => $service->name, 'amount' => $service->amount];
    Session::put('service_bill_cart', $cart);

    return $this->getCartResponse();
  }

  public function removeFromCart(Request $request)
  {
    $request->validate(['service_id' => 'required|exists:services,id']);
    $cart = Session::get('service_bill_cart', []);
    unset($cart[$request->service_id]);
    Session::put('service_bill_cart', $cart);

    return $this->getCartResponse();
  }

  private function getCartResponse()
  {
    $cart = Session::get('service_bill_cart', []);
    $hospital = Hospital::first();
    $tax_percent = $hospital->tax_percent ?? 0;
    $sub_total = collect($cart)->sum('amount');

    return response()->json([
      'cart' => array_values($cart),
      'sub_total' => $sub_total,
      'tax_percent' => $tax_percent,
    ]);
  }
}
