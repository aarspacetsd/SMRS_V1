<?php

namespace App\Http\Controllers\Pages\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Department;

class DepartmentController extends Controller
{
  // Nama fungsi diubah menjadi index()
  public function index()
  {
    $departments = Department::withCount('employees')->get(); // Optimasi dengan withCount
    return view('content.pages.admin.departemen', compact('departments'));
  }

  // Fungsi store() sudah benar
  public function store(Request $request)
  {
    $this->validate($request, ['name' => 'required|unique:departments']);
    Department::create($request->all());
    return back()->with('success', 'Department saved successfully.');
  }

  // Fungsi untuk menyimpan perubahan (update)
  public function update(Request $request, $id)
  {
    $department = Department::find($id);
    $department->name = $request->name;
    $department->save();
    return back()->with('success', 'Department updated successfully');
  }

  // Nama fungsi diubah menjadi destroy()
  public function destroy($id)
  {
    $department = Department::find($id);
    // Logika pengecekan relasi sudah benar
    if (count($department->services) || count($department->employees) || count($department->doctor)) {
      return back()->with('error', 'Department cannot be deleted..');
    }

    $department->delete();
    return back()->with('success', 'Department deleted successfully.');
  }
}
