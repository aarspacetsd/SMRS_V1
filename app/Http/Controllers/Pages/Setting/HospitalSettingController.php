<?php

namespace App\Http\Controllers\Pages\Setting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HospitalSettingController extends Controller
{
  public function index()
  {
    // Logika untuk menampilkan ringkasan dashboard, misal total user, order terbaru, dll.
    return view('dashboard');
  }
}
