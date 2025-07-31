<?php

// Sesuaikan namespace dengan struktur folder proyek Anda
namespace App\Http\Controllers\Pages\LabTest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Test;
use App\Models\TestReference;

class HaematologyTestController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan halaman untuk mengelola referensi tes Haematology.
   */
  public function index()
  {
    // Mengambil tes Haematology beserta relasi referensinya (eager loading)
    $tests = Test::with('test_references')
      ->where('report_type', 'haematology')
      ->get();

    // Mengambil semua referensi yang ada untuk ditampilkan di form
    $test_references = TestReference::orderBy('name')->get();

    return view('content.pages.labtest.haematology', compact('tests', 'test_references'));
  }

  /**
   * Menyimpan atau menyinkronkan referensi ke tes yang dipilih.
   */
  public function syncReferences(Request $request)
  {
    $request->validate([
      'test_id' => 'required|exists:tests,id',
      'test_reference_ids' => 'required|array',
      'test_reference_ids.*' => 'exists:test_references,id', // Memastikan semua ID valid
    ]);

    $test = Test::findOrFail($request->test_id);

    // Sync adalah cara terbaik untuk mengelola relasi many-to-many.
    // Ini akan secara otomatis menambah/menghapus referensi sesuai pilihan.
    $test->test_references()->sync($request->test_reference_ids);

    return back()->with('success', 'Referensi untuk tes ' . $test->name . ' berhasil diperbarui.');
  }
}
