<?php

namespace App\Http\Controllers\Pages\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OpdReportController extends Controller
{
  public function index()
  {
    // Logika untuk menampilkan ringkasan dashboard, misal total user, order terbaru, dll.
    return view('dashboard');
  }
}
