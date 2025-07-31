<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class MenuItem extends Model
{
  use HasFactory;

  protected $guarded = [];

  // Relasi ke submenu (anak)
  public function children()
  {
    return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
  }

  // Relasi ke parent menu (induk)
  public function parent()
  {
    return $this->belongsTo(MenuItem::class, 'parent_id');
  }

  // Relasi ke Roles
  public function roles()
  {
    return $this->belongsToMany(Role::class, 'menu_item_role');
  }
}
