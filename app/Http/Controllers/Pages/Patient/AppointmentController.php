<?php

namespace App\Http\Controllers\Pages\Patient;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // PERUBAHAN: Menambahkan baris ini
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon; // Gunakan Carbon untuk penanganan tanggal yang lebih baik

class AppointmentController extends Controller
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
    // Eager loading untuk mengambil relasi patient dan doctor agar lebih efisien
    $appointments = Appointment::with(['patient', 'doctor.employee'])->latest()->get();

    $patients = Patient::get();

    // Mengambil karyawan dengan tipe 'Doctor' untuk dropdown
    $doctors = Doctor::with('employee')->get();

    return view('content.pages.patient.appointmenttable', compact('appointments', 'patients', 'doctors'));
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
      'doctor_id' => 'required|numeric',
      'patient_id' => 'required|numeric',
      'appointment_date' => 'required|date'
    ]);

    $data = $request->all();
    // Menggunakan Carbon untuk memformat tanggal dengan aman
    $data['appointment_date'] = Carbon::parse($request->appointment_date)->format('Y-m-d');

    Appointment::create($data);

    return redirect()->route('appointments.index')->with('success', 'Appointment saved successfully.');
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    // Logika dari fungsi 'updated' yang lama dipindahkan ke sini
    $this->validate($request, [
      'doctor_id' => 'required|numeric',
      'patient_id' => 'required|numeric',
      'appointment_date' => 'required|date'
    ]);

    $appointment = Appointment::find($id);
    if (!$appointment) {
      return back()->with('error', 'Appointment not found.');
    }

    $data = $request->all();
    $data['appointment_date'] = Carbon::parse($request->appointment_date)->format('Y-m-d');

    $appointment->update($data);

    return redirect()->route('appointments.index')->with('success', 'Appointment updated successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $appointment = Appointment::find($id);
    if ($appointment) {
      $appointment->delete();
      return redirect()->route('appointments.index')->with('success', 'Appointment deleted successfully.');
    }

    return back()->with('error', 'Appointment not found.');
  }

  /**
   * Custom function to toggle the appointment status.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function toggleStatus($id)
  {
    $appointment = Appointment::find($id);
    if ($appointment) {
      // Toggle status dengan cara yang lebih singkat
      $appointment->status = !$appointment->status;
      $appointment->save();
      return back()->with('success', 'Status changed successfully.');
    }

    return back()->with('error', 'Appointment not found.');
  }
}
