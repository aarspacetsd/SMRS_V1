<?php

namespace App\Http\Controllers\Pages\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Test;
use App\Models\PackageTest;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\Hospital;
use App\Models\Report;
use App\Models\TestReport;
use App\Models\PackageSale;
use Illuminate\Support\Facades\Auth;

class PackageTableController extends Controller
{
  /**
   * Instantiate a new controller instance.
   *
   * Apply a middleware to ensure only users with the 'Admin' role can access these methods.
   * You can change 'Admin' to any role you have defined in Spatie, e.g., ['Admin', 'Manager'].
   */
  public function __construct()
  {
    // This middleware uses Spatie's role checking
    $this->middleware('role:admin');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index() // PERUBAHAN: getIndex() menjadi index()
  {
    // Eager load relationships for better performance
    $packages = Package::with('packageTests.test')->get();
    $tests = Test::get();

    // Updated view path
    return view('content.pages.admin.packagetable', compact('packages', 'tests'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $this->validate($request, [
      'name' => 'required|string|max:255',
      'price' => 'required|numeric',
      'test_id' => 'required|array'
    ]);

    $packageData = [
      'name' => $request->name,
      'description' => $request->description,
      'price' => $request->price,
    ];

    // This tax logic is kept from your original code.
    if ($request->with_tax) {
      $tax = Hospital::first()->tax_percent;
      $tax_cal = 100 + $tax;
      // Note: 'amount' is not defined in the view you provided earlier.
      // Assuming it comes from the request.
      $packageData['price'] = $request->amount * 100 / $tax_cal;
    }

    $package = Package::create($packageData);

    // Attach tests to the package
    foreach ($request->test_id as $testId) {
      PackageTest::create([
        'test_id' => $testId,
        'package_id' => $package->id,
      ]);
    }

    return back()->with('success', 'Package Created Successfully.');
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function edit(Request $request)
  {
    $this->validate($request, [
      'id' => 'required|exists:packages,id',
      'name' => 'required|string|max:255',
      'price' => 'required|numeric',
    ]);

    $package = Package::find($request->id);

    $updateData = [
      'name' => $request->name,
      'price' => $request->price,
      'description' => $request->description,
    ];

    // This tax logic is kept from your original code.
    if ($request->with_tax) {
      $tax = Hospital::first()->tax_percent;
      $tax_cal = 100 + $tax;
      // Note: 'amount' is not defined in the view you provided earlier.
      // Assuming it comes from the request.
      $updateData['price'] = $request->amount * 100 / $tax_cal;
    }

    $package->update($updateData);

    // Add new tests if any are provided
    // Note: This does not remove existing tests, it only adds new ones.
    // This is consistent with your original logic.
    if ($request->has('test_id')) {
      foreach ($request->test_id as $testId) {
        PackageTest::create([
          'test_id' => $testId,
          'package_id' => $package->id,
        ]);
      }
    }

    return back()->with('success', 'Package Updated Successfully.');
  }

  /**
   * Remove a specific test from a package.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function packageTestDelete(Request $request)
  {
    $package_test = PackageTest::find($request->id);
    if ($package_test) {
      $package_test->delete();
    }
    return back()->with('success', 'Package Test Deleted Successfully.');
  }

  /**
   * Remove the specified package from storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function delete(Request $request)
  {
    $package = Package::with('packageSales')->find($request->id);

    if ($package && $package->packageSales->count() > 0) {
      return back()->with('error', 'Package cannot be deleted because it has existing sales.');
    }

    if ($package) {
      // Also delete related package tests
      PackageTest::where('package_id', $package->id)->delete();
      $package->delete();
    }

    return back()->with('success', 'Package Deleted Successfully.');
  }


  // --- Sale Related Methods --- //
  // The following methods are related to sales and invoices.
  // Their view paths have been updated based on the provided structure.

  public function sale()
  {
    $packages = Package::all();
    $patients = Patient::all();
    $invoice = Invoice::latest('id')->first();
    $invoice_no = $invoice ? $invoice->id + 1 : 1;

    // Assumed view path for package invoice form
    return view('content.pages.admin.invoices.package_invoice', compact('packages', 'patients', 'invoice_no'));
  }


  public function packageSale(Request $request)
  {
    // This complex method is kept as is, only the final view path is updated.
    $package = Package::find($request->package_id);
    $tax_percent = Hospital::first()->tax_percent;
    $sub_total = $package->price;

    if ($request->discount) {
      $discount = $request->discount;
      $sub_total = $sub_total - $discount;
    }

    $tax_amount = $sub_total * $tax_percent / 100;
    $cash = $request->cash;
    $total_amount = $sub_total + $tax_amount;

    $invoiceData = [
      'sub_total' => $package->price,
      'discount' => $request->discount,
      'tax_amount' => $tax_amount,
      'total_amount' => $total_amount,
      'patient_id' => $request->patient_id,
      'invoice_no' => $request->invoice_no,
      'comment' => $request->comment,
      'payment_type' => $request->payment_type,
      'user_id' => Auth::id(),
      'cash' => $request->cash,
    ];

    $invoice = Invoice::create($invoiceData);

    PackageSale::create([
      'patient_id' => $request->patient_id,
      'package_id' => $request->package_id,
      'invoice_id' => $invoice->id,
      'package_price' => $package->price,
    ]);

    $report = Report::create(['patient_id' => $request->patient_id]);
    $package_tests = $package->packageTests()->with('test')->get();

    foreach ($package_tests as $package_test) {
      TestReport::create([
        'report_id' => $report->id,
        'test_id' => $package_test->test_id,
        'report_type' => $package_test->test->report_type,
      ]);
    }

    $invoice['return'] = $cash - $total_amount;
    $invoice['opd'] = false;
    $invoice['services'] = false;
    $invoice['packages'] = true;

    // Assumed view path for the completion page
    return view('content.pages.admin.invoices.complete', compact('invoice'));
  }

  /**
   * This method returns raw HTML, likely for an AJAX request.
   * No changes needed here.
   */
  public function packageSales($package_id)
  {
    $package = Package::find($package_id);
    $hospital = Hospital::first();
    $tax_amount = $package->price * $hospital->tax_percent / 100;
    $total_amount = $package->price + $tax_amount;
    $sn = 0;
    $list = '';
    $list .= '<table class="table">
                <thead>
                  <tr>
                    <th>SN.</th>
                    <th>Particular</th>
                    <th>Amount</th>
                    <th>Remove</th>
                  </tr>
                </thead>
                <tbody>';


    $list .= '<tr><td>1</td><td>' . $package->name . '</td><td>Rs.' . $package->price . '</td><td><a href="/package/sale"><span class="btn-sm btn-danger glyphicon glyphicon-remove"></span></a></button></td></tr>
        <div class="total_field">
        <tr><td></td><td></td><td></td><td>Sub Total:Rs. ' . $package->price . '</td></tr>
        <tr><td></td><td></td><td></td><td>HST(' . $hospital->tax_percent . '%):Rs. ' . $tax_amount . '</td></tr><input type="hidden" id="package_charge" value="' . $package->price . '"><input type="hidden" id="tax_percent" value="' . $hospital->tax_percent . '">
        <tr class="success"><td></td><td></td><td></td><td >Total Amount:Rs. ' . $total_amount . '</td></tr></div>  </tbody>

                </table>';
    return $list;
  }
}
