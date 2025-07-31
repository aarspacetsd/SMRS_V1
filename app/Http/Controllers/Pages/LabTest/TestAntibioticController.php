<?php

namespace App\Http\Controllers\Pages\LabTest;

use App\Http\Controllers\Controller;
use App\Models\TestAntibiotic;
use Illuminate\Http\Request;

class TestAntibioticController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menyimpan antibiotik baru.
   */
  public function store(Request $request)
  {
    $request->validate(['name' => 'required|string|max:255|unique:test_antibiotics,name']);
    TestAntibiotic::create($request->all());
    return back()->with('success', 'Antibiotik berhasil ditambahkan.');
  }

  /**
   * Memperbarui antibiotik yang ada.
   */
  public function update(Request $request, TestAntibiotic $test_antibiotic)
  {
    $request->validate(['name' => 'required|string|max:255|unique:test_antibiotics,name,' . $test_antibiotic->id]);
    $test_antibiotic->update($request->all());
    return back()->with('success', 'Antibiotik berhasil diperbarui.');
  }

  /**
   * Menghapus antibiotik.
   */
  public function destroy(TestAntibiotic $test_antibiotic)
  {
    // Tambahkan pengecekan jika antibiotik sedang digunakan oleh sebuah tes
    // if ($test_antibiotic->tests()->exists()) {
    //     return back()->with('error', 'Antibiotik tidak dapat dihapus karena sedang digunakan.');
    // }
    $test_antibiotic->delete();
    return back()->with('success', 'Antibiotik berhasil dihapus.');
  }
}
