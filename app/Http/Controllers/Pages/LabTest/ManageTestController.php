<?php

// Sesuaikan namespace dengan struktur folder proyek Anda
namespace App\Http\Controllers\Pages\LabTest;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\Service;
use Illuminate\Http\Request;

class ManageTestController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Menampilkan halaman manajemen tes laboratorium.
   */
  public function index()
  {
    // Mengambil tes beserta relasi layanannya (eager loading)
    $tests = Test::with('service')->get();
    $services = Service::orderBy('name')->get();
    return view('content.pages.labtest.managetest', compact('tests', 'services'));
  }

  /**
   * Menyimpan tes baru ke database.
   */
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255|unique:tests,name',
      'service_id' => 'required|exists:services,id',
      'report_type' => 'required|string',
      'status' => 'boolean',
    ]);

    Test::create($request->all());

    return back()->with('success', 'Tes berhasil disimpan.');
  }

  /**
   * Memperbarui tes yang ada.
   */
  public function update(Request $request, Test $test)
  {
    $request->validate([
      'name' => 'required|string|max:255|unique:tests,name,' . $test->id,
      'service_id' => 'required|exists:services,id',
      'report_type' => 'required|string',
      'status' => 'boolean',
    ]);

    // Logika untuk detach relasi jika report_type berubah
    if ($test->report_type != $request->report_type) {
      if (in_array($test->report_type, ['hematology', 'biochemistry'])) {
        $test->test_references()->detach();
      }
      // Tambahkan logika detach lain jika perlu
    }

    $test->update($request->all());

    return back()->with('success', 'Tes berhasil diperbarui.');
  }

  /**
   * Mengubah status aktif/tidak aktif dari sebuah tes.
   */
  public function toggleStatus(Test $test)
  {
    $test->status = !$test->status;
    $test->save();

    return back()->with('success', 'Status tes berhasil diubah.');
  }


  /**
   * Menghapus tes dari database.
   */
  public function destroy(Test $test)
  {
    // Cek relasi sebelum menghapus
    if ($test->test_references()->exists() || $test->test_reports()->exists()) {
      return back()->with('error', 'Tes tidak dapat dihapus karena memiliki data referensi atau laporan terkait.');
    }

    $test->delete();

    return back()->with('success', 'Tes berhasil dihapus.');
  }
}
