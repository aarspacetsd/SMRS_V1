<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard dengan proteksi role
Route::middleware(['auth', 'verified', 'role:admin'])->get('/admin-dashboard', function () {
    return view('admin.dashboard'); // Halaman dashboard untuk admin
})->name('admin.dashboard');

Route::middleware(['auth', 'verified', 'role:pegawai'])->get('/pegawai-dashboard', function () {
    return view('pegawai.dashboard'); // Halaman dashboard untuk pegawai
})->name('pegawai.dashboard');

Route::middleware(['auth', 'verified', 'role:user'])->get('/user-dashboard', function () {
    return view('users.dashboard'); // Halaman dashboard untuk user biasa
})->name('users.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/cek-role', function () {
    return 'Anda adalah admin';
})->middleware(['role:admin']);



require __DIR__.'/auth.php';
