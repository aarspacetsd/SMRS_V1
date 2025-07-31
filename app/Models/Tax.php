<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'percent',
  ];

  /**
   * Mendefinisikan bahwa setiap data pajak dimiliki oleh satu rumah sakit.
   */
  public function hospital()
  {
    return $this->belongsTo(Hospital::class);
  }
}
