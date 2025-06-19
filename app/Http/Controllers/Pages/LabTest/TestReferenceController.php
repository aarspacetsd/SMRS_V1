<?php

namespace App\Http\Controllers\Pages\LabTest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestReferenceController extends Controller
{
  public function index()
  {
    // Logika untuk menampilkan ringkasan dashboard, misal total user, order terbaru, dll.
    return view('dashboard');
  }
}
