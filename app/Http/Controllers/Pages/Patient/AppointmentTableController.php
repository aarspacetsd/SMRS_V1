<?php

namespace App\Http\Controllers\Pages\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 1. IMPORT SEMUA MODEL YANG DIPERLUKAN
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
// Nantinya Anda akan mengganti validasi dengan Form Request
// use App\Http\Requests\StoreAppointmentRequest;
// use App\Http\Requests\UpdateAppointmentRequest;
use Carbon\Carbon;

class AppointmentTableController extends Controller
{
  /**
   * Menampilkan daftar appointment dengan paginasi dan data relasi.
   */
  public function index()
  {
    // 2. GUNAKAN EAGER LOADING DAN PAGINASI
    $appointments = Appointment::with(['patient', 'doctor'])
      ->latest() // Urutkan dari yang terbaru
      ->paginate(15); // Ambil 15 data per halaman

    return view('appointments.index', compact('appointments'));
  }

  /**
   * Menampilkan form untuk membuat appointment baru.
   */
  public function create()
  {
    // Ambil data pasien dan dokter untuk dropdown di form
    $patients = Patient::select('id', 'name')->get();
    $doctors = Doctor::select('id', 'name')->get();

    return view('appointments.create', compact('patients', 'doctors'));
  }

  /**
   * Menyimpan appointment baru ke database.
   * Ganti Request dengan StoreAppointmentRequest setelah Anda membuatnya.
   */
  public function store(Request $request) // ganti menjadi StoreAppointmentRequest $request
  {
    // 3. VALIDASI (Gunakan Form Request untuk kode yang lebih bersih)
    $validatedData = $request->validate([
      'doctor_id' => 'required|exists:doctors,id',
      'patient_id' => 'required|exists:patients,id',
      'appointment_date' => 'required|date',
      'appointment_time' => 'required',
      'description' => 'nullable|string',
    ]);

    // Buat appointment baru (pastikan $fillable di model sudah di-set)
    Appointment::create($validatedData);

    return redirect()->route('appointments.index')->with('success', 'Appointment saved successfully.');
  }

  /**
   * Menampilkan form untuk mengedit appointment.
   */
  public function edit($id)
  {
    $appointment = Appointment::findOrFail($id);
    $patients = Patient::select('id', 'name')->get();
    $doctors = Doctor::select('id', 'name')->get();

    return view('appointments.edit', compact('appointment', 'patients', 'doctors'));
  }

  /**
   * Memproses update data appointment.
   * Ganti Request dengan UpdateAppointmentRequest setelah Anda membuatnya.
   */
  public function update(Request $request, $id) // ganti menjadi UpdateAppointmentRequest $request
  {
    $appointment = Appointment::findOrFail($id);

    $validatedData = $request->validate([
      'doctor_id' => 'required|exists:doctors,id',
      'patient_id' => 'required|exists:patients,id',
      'appointment_date' => 'required|date',
      'appointment_time' => 'required',
      'description' => 'nullable|string',
    ]);

    $appointment->update($validatedData);

    return redirect()->route('appointments.index')->with('success', 'Appointment updated successfully.');
  }

  /**
   * Menghapus appointment dari database.
   */
  public function destroy($id)
  {
    $appointment = Appointment::findOrFail($id);
    $appointment->delete();

    return back()->with('success', 'Appointment deleted successfully.');
  }

  /**
   * Mengubah status appointment (misal: dari pending ke confirmed).
   */
  public function toggleStatus($id)
  {
    $appointment = Appointment::findOrFail($id);

    // Cara lebih ringkas untuk toggle
    $appointment->status = !$appointment->status; // Asumsi 1 = true, 0 = false
    $appointment->save();

    return back()->with('success', 'Status changed successfully.');
  }
}
