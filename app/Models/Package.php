<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
  protected $fillable = ['name', 'description', 'price'];

  /**
   * Mendefinisikan relasi ke PackageSale.
   */
  public function packageSales()
  {
    return $this->hasMany('App\Models\PackageSale');
  }
  public function tests()
  {
    return $this->belongsToMany(Test::class, 'package_tests');
  }
}
