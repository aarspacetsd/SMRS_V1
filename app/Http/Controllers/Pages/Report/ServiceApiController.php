<?php

namespace App\Http\Controllers\Pages\Report;

use App\Http\Controllers\Controllers\Api; // Disarankan menempatkan API controller di namespace Api

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceApiController extends Controller
{
  /**
   * Menampilkan detail satu layanan spesifik.
   *
   * @param \App\Models\Service $service
   * @return \Illuminate\Http\JsonResponse
   */
  public function show(Service $service)
  {
    // Route model binding akan otomatis menangani jika service tidak ditemukan (404)
    // Memuat relasi departemennya jika diperlukan
    $service->load('department');

    return response()->json($service);
  }

  // Catatan: Method untuk update dan delete sebaiknya ditangani oleh ServiceController utama
  // yang sudah ada untuk menjaga konsistensi dan keamanan (misalnya, validasi, cek relasi).
  // Jika Anda benar-benar butuh API untuk update/delete, kodenya bisa ditambahkan di sini
  // dengan validasi yang ketat.
}
