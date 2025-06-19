<?php

namespace App\Http\Controllers\Pages\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Department;
use App\Models\Hospital;

class ServiceTableController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    // Menggunakan with('department') untuk eager loading (lebih efisien)
    $services = Service::with('department')->get();
    $departments = Department::select('id', 'name')->get();

    // Asumsi view ada di 'resources/views/services/index.blade.php'
    return view('content.pages.admin.servicetable', compact('services', 'departments'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255|unique:services',
      'amount' => 'required|numeric',
      'department_id' => 'required|exists:departments,id'
    ]);

    // PERBAIKAN: Kalkulasi pajak dilakukan sebelum data dimasukkan ke database
    if ($request->with_tax) {
      $tax = Hospital::first()->tax_percent ?? 0;
      $tax_cal = 100 + $tax;
      if ($tax_cal > 0) {
        // Simpan hasil kalkulasi ke dalam array yang akan disimpan
        $validated['amount'] = ($request->amount * 100) / $tax_cal;
      }
    }

    Service::create($validated);

    return back()->with('success', 'Service saved successfully.');
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\Service  $service
   * @return \Illuminate\Http\Response
   */
  // PERUBAHAN: Menggunakan Route Model Binding (Service $service) dan mengubah nama method dari 'edit' menjadi 'update'
  public function update(Request $request, Service $service)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255|unique:services,name,' . $service->id,
      'amount' => 'required|numeric',
      'department_id' => 'required|exists:departments,id'
    ]);

    // PERBAIKAN: Bug kalkulasi pajak diperbaiki
    if ($request->with_tax) {
      $tax = Hospital::first()->tax_percent ?? 0;
      $tax_cal = 100 + $tax;
      if ($tax_cal > 0) {
        $validated['amount'] = ($request->amount * 100) / $tax_cal;
      }
    }

    $service->update($validated);

    return back()->with('success', 'Service updated successfully.');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\Service  $service
   * @return \Illuminate\Http\Response
   */
  // PERUBAHAN: Menggunakan Route Model Binding dan mengubah nama method dari 'delete' menjadi 'destroy'
  public function destroy(Service $service)
  {
    // Memuat relasi untuk pengecekan (lebih eksplisit)
    $service->load('service_sales', 'tests');

    if ($service->service_sales->count() > 0 || $service->tests->count() > 0) {
      return back()->with('error', 'Service cannot be deleted because it is in use.');
    }

    $service->delete();

    return back()->with('success', 'Service deleted successfully.');
  }
}
