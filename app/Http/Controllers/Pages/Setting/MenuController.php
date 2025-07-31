<?php

namespace App\Http\Controllers\Pages\Setting;


use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class MenuController extends Controller
{
  public function index()
  {
    $menus = MenuItem::whereNull('parent_id')->with('children')->orderBy('order')->get();
    return view('content.pages.settings.menusetings', compact('menus'));
  }

  public function create()
  {
    $roles = Role::all();
    $parentMenus = MenuItem::whereNull('parent_id')->get();
    return view('content.pages.settings.menusetings', compact('roles', 'parentMenus'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'url' => 'required|string|max:255',
      'icon' => 'nullable|string|max:255',
      'parent_id' => 'nullable|exists:menu_items,id',
      'roles' => 'nullable|array',
      'roles.*' => 'exists:roles,id',
    ]);

    $menuItem = MenuItem::create($request->except('roles'));

    if ($request->has('roles')) {
      $menuItem->roles()->sync($request->roles);
    }

    return redirect()->route('admin.menus.index')->with('success', 'Menu item berhasil dibuat.');
  }

  public function edit(MenuItem $menu)
  {
    $roles = Role::all();
    $parentMenus = MenuItem::whereNull('parent_id')->where('id', '!=', $menu->id)->get();
    return view('content.pages.settings.menusetings', compact('menu', 'roles', 'parentMenus'));
  }

  public function update(Request $request, MenuItem $menu)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'url' => 'required|string|max:255',
      'icon' => 'nullable|string|max:255',
      'parent_id' => 'nullable|exists:menu_items,id',
      'roles' => 'nullable|array',
      'roles.*' => 'exists:roles,id',
    ]);

    $menu->update($request->except('roles'));
    $menu->roles()->sync($request->roles ?? []);

    return redirect()->route('admin.menus.index')->with('success', 'Menu item berhasil diperbarui.');
  }

  public function destroy(MenuItem $menu)
  {
    $menu->delete();
    return redirect()->route('admin.menus.index')->with('success', 'Menu item berhasil dihapus.');
  }
}
