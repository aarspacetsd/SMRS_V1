<?php

namespace App\Http\Controllers\Pages\Admin;

use App\Models\Doctor;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Hospital;
use App\Models\OpdSales;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DoctorController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $doctors = Doctor::all();
    $employees = Employee::where('type', 'Doctor')->get();
    //$days = explode(',',$doctors->working_day);
    return view('content.pages.admin.doctor', compact('doctors', 'employees'));

    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $this->validate($request, ['employee_id' => 'required']);
    $data = $request->all();

    // Pindahkan logika ke dalam if
    if ($request->with_tax) {
      $hospital = Hospital::first();
      // Pastikan data hospital ada sebelum mengambil pajak
      if ($hospital) {
        $tax = $hospital->tax_percent;
        $tax_cal = 100 + $tax;
        $data['fee'] = $request->fee * 100 / $tax_cal;
        $data['opd_charge'] = $request->opd_charge * 100 / $tax_cal;
      } else {
        // Opsional: Beri pesan error jika pajak dicentang tapi data hospital tidak ada
        return back()->with('error', 'Pengaturan pajak tidak ditemukan.');
      }
    }

    Doctor::create($data);
    return redirect()->route('doctors.index')->with('success', 'Doctor saved successfully.');
  }

  // public function edit($id)
  // {
  //     //return $id;
  //     $opd = Doctor::find($id);
  //     //return $opd;

  //     if($opd->employee->status == 0)
  //     {
  //         $status['status'] = 1;
  //     }else
  //     {
  //         $status['status'] = 0;
  //     }

  //     $opd->update($status);

  //     return back()->with('success', 'Doctor active Successfully');
  //     //
  // }

  public function edit($id)
  {
    $opd = OpdSales::find($id);

    // Check if the opd record is found
    if (!$opd) {
      return back()->with('error', 'OPD record not found.');
    }

    // Check if the doctor relationship is loaded
    if (!$opd->doctor) {
      return back()->with('error', 'Doctor data not found for the OPD record.');
    }

    // Toggle the status
    $status = ($opd->status == 0) ? 1 : 0;
    $opd->update(['status' => $status]);

    return back()->with('success', 'OPD status updated successfully.');
  }


  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $doctor = Doctor::find($id);
    $employees = Employee::get();
    return view('doctors.profile', compact('doctor', 'employees'));
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */

  public function update(Request $request, $id)
  {
    $doctor = Doctor::find($id);
    if (!$doctor) {
      return back()->with('error', 'Dokter tidak ditemukan.');
    }

    $data = $request->all();

    // Terapkan logika yang sama di sini
    if ($request->with_tax) {
      $hospital = Hospital::first();
      if ($hospital) {
        $tax = $hospital->tax_percent;
        $tax_cal = 100 + $tax;
        $data['fee'] = $request->fee * 100 / $tax_cal;
        $data['opd_charge'] = $request->opd_charge * 100 / $tax_cal;
      } else {
        return back()->with('error', 'Pengaturan pajak tidak ditemukan.');
      }
    }

    $doctor->update($data);
    return redirect()->route('doctors.index')->with('success', 'Doctor updated successfully.');
  }

  // public function update(Request $request, $id)
  // {

  //     $doctor = Doctor::find ( $id );

  //     if($request->with_tax) {

  //         $tax_cal = 100 + $tax;
  //         $request['fee'] = $request->fee*100/$tax_cal;
  //         $request['opd_charge'] = $request->opd_charge*100/$tax_cal;
  //     }

  //     $doctor->update($request->all());
  //     $departments = Department::get();
  //      return back()->with('success', 'Doctor Updated Successfully');
  //     //
  // }


  public function destroy($id)
  {
    $doctor = Doctor::find($id);

    if (count($doctor->opd_sales) || count($doctor->reports) || count($doctor->doctor_referred) || count($doctor->appointments)) {
      return back()->with('error', 'Doctor cannot delted..');
    }
    $doctor->delete();
    return redirect()->route('doctors.index')->with('success', 'Doctor Deletetd Successfully');
  }
}
