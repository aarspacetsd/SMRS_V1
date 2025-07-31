<?php

// Sesuaikan namespace dengan struktur folder proyek Anda
namespace App\Http\Controllers\Pages\LabTest;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\TestStain;
use Illuminate\Http\Request;

class StainTestController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan halaman manajemen Stain Test.
   */
  public function index()
  {
    // Mengambil data stain beserta relasi tes-nya (eager loading)
    $test_stains = TestStain::with('test')->get();
    // Mengambil tes dengan tipe 'stain' untuk form
    $tests = Test::where('report_type', 'stain')->get();

    return view('content.pages.labtest.staintest', compact('test_stains', 'tests'));
  }

  /**
   * Menyimpan data Stain Test baru.
   */
  public function store(Request $request)
  {
    $request->validate([
      'test_id' => 'required|exists:tests,id|unique:test_stains,test_id',
      'test_names' => 'required|array|min:1',
      'test_names.*' => 'required|string|max:255',
    ]);

    TestStain::create([
      'test_id' => $request->test_id,
      'test_names' => $request->test_names, // Model akan otomatis mengubah ini ke JSON
    ]);

    return back()->with('success', 'Stain Test berhasil dibuat.');
  }

  /**
   * Memperbarui data Stain Test yang ada.
   */
  public function update(Request $request, TestStain $stain_test)
  {
    $request->validate([
      'test_id' => 'required|exists:tests,id|unique:test_stains,test_id,' . $stain_test->id,
      'test_names' => 'required|array|min:1',
      'test_names.*' => 'required|string|max:255',
    ]);

    $stain_test->update([
      'test_id' => $request->test_id,
      'test_names' => $request->test_names,
    ]);

    return back()->with('success', 'Stain Test berhasil diperbarui.');
  }

  /**
   * Menghapus data Stain Test.
   */
  public function destroy(TestStain $stain_test)
  {
    $stain_test->delete();
    return back()->with('success', 'Stain Test berhasil dihapus.');
  }
}
