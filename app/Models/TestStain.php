<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestStain extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['test_id', 'test_names'];

  /**
   * --- TAMBAHKAN PROPERTI INI ---
   *
   * Memberitahu Laravel untuk menangani kolom 'test_names' sebagai array.
   * Secara otomatis akan di-encode ke JSON saat disimpan dan di-decode saat diambil.
   */
  protected $casts = [
    'test_names' => 'array',
  ];

  /**
   * Mendefinisikan relasi ke model Test.
   */
  public function test()
  {
    return $this->belongsTo(Test::class);
  }
}
