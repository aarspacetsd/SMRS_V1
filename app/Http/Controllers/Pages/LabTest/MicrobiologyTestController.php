<?php

// Sesuaikan namespace dengan struktur folder proyek Anda
namespace App\Http\Controllers\Pages\LabTest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Test;
use App\Models\TestAntibiotic;

class MicrobiologyTestController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan halaman utama untuk tes mikrobiologi.
   */
  public function index()
  {
    // Mengambil tes mikrobiologi beserta relasi antibiotiknya
    $tests = Test::with('test_antibiotics')
      ->where('report_type', 'microbiology')
      ->get();

    // Mengambil semua antibiotik yang ada untuk form
    $test_antibiotics = TestAntibiotic::orderBy('name')->get();

    return view('content.pages.labtest.microbiologytest', compact('tests', 'test_antibiotics'));
  }

  /**
   * Menyinkronkan antibiotik yang dipilih ke tes mikrobiologi.
   */
  public function syncAntibiotics(Request $request)
  {
    $request->validate([
      'test_id' => 'required|exists:tests,id',
      'test_antibiotics_ids' => 'required|array',
      'test_antibiotics_ids.*' => 'exists:test_antibiotics,id',
    ]);

    $test = Test::findOrFail($request->test_id);
    $test->test_antibiotics()->sync($request->test_antibiotics_ids);

    return back()->with('success', 'Antibiotik untuk tes ' . $test->name . ' berhasil diperbarui.');
  }
}
