<?php

// Sesuaikan namespace dengan struktur folder proyek Anda
namespace App\Http\Controllers\Pages\LabTest;

use App\Http\Controllers\Controller;
use App\Models\TestReference;
use Illuminate\Http\Request;

class TestReferenceController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan halaman manajemen referensi tes.
   */
  public function index()
  {
    // Mengambil semua referensi beserta relasi induknya (parent)
    $test_references = TestReference::with('parent')->orderBy('name')->get();

    // Mengambil referensi level atas untuk pilihan 'Parent Test' di form
    $parent_references = TestReference::whereNull('parent_id')->orderBy('name')->get();

    return view('content.pages.labtest.testreference', compact('test_references', 'parent_references'));
  }

  /**
   * Menyimpan referensi tes baru.
   */
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255|unique:test_references,name',
      'unit' => 'nullable|string|max:50',
      'ref_range' => 'nullable|string|max:255',
      'parent_id' => 'nullable|exists:test_references,id'
    ]);

    TestReference::create($request->all());

    return back()->with('success', 'Referensi Tes berhasil disimpan.');
  }

  /**
   * Memperbarui referensi tes yang ada.
   */
  public function update(Request $request, TestReference $test_reference)
  {
    $request->validate([
      'name' => 'required|string|max:255|unique:test_references,name,' . $test_reference->id,
      'unit' => 'nullable|string|max:50',
      'ref_range' => 'nullable|string|max:255',
      'parent_id' => 'nullable|exists:test_references,id'
    ]);

    $test_reference->update($request->all());

    return back()->with('success', 'Referensi Tes berhasil diperbarui.');
  }

  /**
   * Menghapus referensi tes.
   */
  public function destroy(TestReference $test_reference)
  {
    // Mencegah penghapusan jika memiliki turunan (children)
    if ($test_reference->children()->exists()) {
      return back()->with('error', 'Referensi tidak dapat dihapus karena memiliki turunan.');
    }

    $test_reference->delete();

    return back()->with('success', 'Referensi Tes berhasil dihapus.');
  }
}
