<?php

// Namespace disesuaikan dengan lokasi file Anda
namespace App\Http\Controllers\Pages\LabTest;

// PENTING: Karena namespace berubah, Anda harus mengimpor Controller utama
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Test;
use App\Models\TestExamination;

class ExaminationTestController extends Controller
{
  /**
   * Menampilkan daftar tes pemeriksaan.
   *
   * @return \Illuminate\View\View
   */
  public function index()
  {
    // Mengambil data untuk dropdown form
    $tests = Test::where('report_type', 'examination')->select('id', 'name')->get();

    // Mengambil data pemeriksaan, diurutkan dari yang terbaru, dan menggunakan paginasi
    $test_examinations = TestExamination::with('test') // Eager load relasi 'test'
      ->latest()
      ->paginate(15);

    // Pastikan path view ini benar sesuai struktur folder Anda
    return view('content.pages.labtest.examinationstest', compact('tests', 'test_examinations'));
  }

  /**
   * Menyimpan data pemeriksaan baru.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(Request $request)
  {
    $validatedData = $request->validate([
      'test_id' => 'required|exists:tests,id',
      'macroscopic' => 'required|array|min:1',
      'macroscopic.*' => 'required|string|max:255',
      'microscopic' => 'required|array|min:1',
      'microscopic.*' => 'required|string|max:255',
    ]);

    TestExamination::create([
      'test_id' => $validatedData['test_id'],
      'macroscopics' => json_encode($validatedData['macroscopic']),
      'microscopics' => json_encode($validatedData['microscopic']),
    ]);

    // Menggunakan back() lebih fleksibel untuk route ber-prefix
    return back()->with('success', 'Examination test created successfully.');
  }

  /**
   * Mengupdate data pemeriksaan yang ada.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\TestExamination  $examination
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, TestExamination $examination)
  {
    $validatedData = $request->validate([
      'test_id' => 'required|exists:tests,id',
      'macroscopic' => 'required|array|min:1',
      'macroscopic.*' => 'required|string|max:255',
      'microscopic' => 'required|array|min:1',
      'microscopic.*' => 'required|string|max:255',
    ]);

    $examination->update([
      'test_id' => $validatedData['test_id'],
      'macroscopics' => json_encode($validatedData['macroscopic']),
      'microscopics' => json_encode($validatedData['microscopic']),
    ]);

    return back()->with('success', 'Examination test updated successfully.');
  }

  /**
   * Menghapus data pemeriksaan.
   *
   * @param  \App\Models\TestExamination  $examination
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(TestExamination $examination)
  {
    $examination->delete();
    return back()->with('success', 'Examination test deleted successfully.');
  }
}
