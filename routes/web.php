<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Dashboard

use App\Http\Controllers\Pages\DashboardController;

// --- Controllers Modul Admin (Pages/Admin/) ---
use App\Http\Controllers\Pages\Admin\DepartmentController;
use App\Http\Controllers\Pages\Admin\DoctorController;
use App\Http\Controllers\Pages\Admin\EmployeeController;
use App\Http\Controllers\Pages\Admin\PackageTableController;
use App\Http\Controllers\Pages\Admin\ServiceTableController;
use App\Http\Controllers\Pages\Admin\DoctorApiController;

// --- Controllers Modul Patient (Pages/Patient/) ---
use App\Http\Controllers\Pages\Patient\PatientTableController; // Untuk Patient Table
use App\Http\Controllers\Pages\Patient\AppointmentController; // Untuk Appointment Table

// --- Controllers Modul Report (Pages/Report/) ---
use App\Http\Controllers\Pages\Report\ServiceApiController;
use App\Http\Controllers\Pages\Report\ServiceController;
use App\Http\Controllers\Pages\Report\PackageController; // Untuk Package Reports

// --- Controllers Modul Invoice/Bill (Pages/InvoiceBill/) ---
use App\Http\Controllers\Pages\InvoiceBill\InvoiceController;
use App\Http\Controllers\Pages\InvoiceBill\OpdInvoiceController;
use App\Http\Controllers\Pages\InvoiceBill\PackageInvoiceController;
use App\Http\Controllers\Pages\InvoiceBill\ServiceBillController;

// --- Controllers Modul Lab Test (Pages/LabTest/) ---
use App\Http\Controllers\Pages\LabTest\ExaminationTestController;
use App\Http\Controllers\Pages\LabTest\HaematologyTestController;
use App\Http\Controllers\Pages\LabTest\ManageTestController;
use App\Http\Controllers\Pages\LabTest\MicrobiologyTestController;
use App\Http\Controllers\Pages\LabTest\TestAntibioticController;
use App\Http\Controllers\Pages\LabTest\StainTestController;
use App\Http\Controllers\Pages\LabTest\TestReferenceController;
use App\Http\Controllers\Pages\LabTest\ReportController;


// --- Controllers Modul Setting (Pages/Setting/) ---
use App\Http\Controllers\Pages\Setting\UserController;
use App\Http\Controllers\Pages\Setting\MenuController; // Untuk Manage User (bukan admin/user)
use App\Http\Controllers\Pages\Setting\HospitalSettingController;
use App\Http\Controllers\Pages\Setting\RoleController; // Untuk Manage Role

// Route::get('/', function () {
//   return view('auth.login');
// });

Route::get('/', function () {
  if (auth()->check()) {
    return redirect()->route('dashboard.index');
  }
  return view('auth.login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


// --- Route Group Utama untuk Dashboard ---
Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {

  // 1. Dashboard Utama
  Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
  // 2. Modul Admin
  Route::prefix('admin')->group(function () {
    Route::resource('departments', DepartmentController::class);
    Route::resource('doctors', DoctorController::class);

    Route::resource('employees', EmployeeController::class);
    // Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    // Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    // Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    // Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    // // Perhatikan: Rute untuk update sekarang menggunakan POST, bukan PUT/PATCH
    // Route::post('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::resource('/packages', PackageTableController::class);
    Route::post('/packages/test/delete', [PackageTableController::class, 'packageTestDelete'])->name('packages.test.delete');
    Route::post('/packages/delete', [PackageTableController::class, 'delete'])->name('packages.delete');
    Route::resource('/services', ServiceTableController::class);
  });

  // 3. Modul Patient
  Route::prefix('patient')->group(function () {
    Route::resource('/patients', PatientTableController::class);
    Route::resource('appointments', AppointmentController::class);
    Route::post('appointments/{appointment}/toggle-status', [AppointmentController::class, 'toggleStatus'])->name('appointments.toggleStatus');
  });
  // 3. Reports
  Route::prefix('reports')->name('reports.')->group(function () {

    Route::get('/opd', [OpdInvoiceController::class, 'report'])->name('opd.report');
    Route::resource('packages', PackageController::class)->except(['show', 'create', 'edit']);
    Route::get('/service-report', [ServiceController::class, 'index'])->name('service.index');
  });
  // 5. Invoice
  Route::prefix('invoice-bill')->name('invoice-bill.')->group(function () {
    Route::get('/opd-invoice/create', [OpdInvoiceController::class, 'create'])->name('opd.create');
    Route::post('/opd-invoice', [OpdInvoiceController::class, 'store'])->name('opd.store');
    Route::get('/all-invoice-report', [InvoiceController::class, 'index'])->name('all-invoice.index');
    Route::get('/package-invoice/create', [PackageInvoiceController::class, 'create'])->name('package-invoice.create');
    Route::post('/package-invoice', [PackageInvoiceController::class, 'store'])->name('package-invoice.store');
    Route::get('/service-bill/create', [ServiceBillController::class, 'create'])->name('service-bill.create');
    Route::post('/service-bill', [ServiceBillController::class, 'store'])->name('service-bill.store');
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/{report}/edit', [ReportController::class, 'edit'])->name('edit');
    Route::put('/{report}', [ReportController::class, 'update'])->name('update');
    Route::get('/{report}/pdf', [ReportController::class, 'generatePdf'])->name('generatePdf');
  });

  // 6. Modul Lab Test
  Route::prefix('lab-test')->group(function () {
    Route::resource('examinations', ExaminationTestController::class)->only([
      'index',
      'store',
      'update',
      'destroy'
    ]);
    Route::resource('tests', ManageTestController::class)->except([
      'create',
      'edit',
      'show' // Metode ini tidak kita gunakan karena form ada di Offcanvas
    ]);

    // Route khusus untuk mengubah status
    Route::post('tests/{test}/toggle-status', [ManageTestController::class, 'toggleStatus'])->name('tests.toggleStatus');
    Route::resource('tests', ManageTestController::class)->except(['show', 'create', 'edit']);
    Route::get('/haematology-test', [HaematologyTestController::class, 'index'])->name('haematology-test.index');
    Route::post('/haematology-test', [HaematologyTestController::class, 'syncReferences'])->name('haematology-test.sync');
    Route::resource('test-references', TestReferenceController::class)->except(['show', 'create', 'edit']);
    Route::resource('stain-tests', StainTestController::class)->except(['show', 'create', 'edit']);
    Route::get('/microbiology-test', [MicrobiologyTestController::class, 'index'])->name('microbiology-test.index');
    Route::post('/microbiology-test', [MicrobiologyTestController::class, 'syncAntibiotics'])->name('microbiology-test.sync');
    Route::get('/reports', [ReportController::class, 'index'])->name('index');
    Route::get('/{report}/edit', [ReportController::class, 'edit'])->name('edit');
    Route::put('/{report}', [ReportController::class, 'update'])->name('update');
    Route::get('/{report}/pdf', [ReportController::class, 'generatePdf'])->name('generatePdf');
    Route::resource('test-antibiotics', TestAntibioticController::class)->only(['store', 'update', 'destroy']);
  });
  // 7. Modul Setting
  Route::prefix('settings')->group(function () {
    // Rute untuk menampilkan halaman pengaturan utama
    Route::get('/hospital', [HospitalSettingController::class, 'setting'])->name('hospital.setting');
    // Rute untuk memperbarui data rumah sakit
    Route::put('/hospital/{id}', [HospitalSettingController::class, 'updateHospital'])->name('hospital.update');
    // Rute untuk memperbarui data pajak
    Route::put('/tax/{id}', [HospitalSettingController::class, 'updateTax'])->name('tax.update');
    // Rute untuk membuat cadangan database
    Route::post('/backup', [HospitalSettingController::class, 'backup'])->name('backup.create');
    Route::resource('users', UserController::class); // Ini akan membuat rute untuk index, create, store, show, edit, update, destroy
    Route::resource('roles', RoleController::class);
    Route::resource('menus', MenuController::class);
  });
  Route::get('/doctors/{id}/schedule', [DoctorApiController::class, 'getSchedule']);
});

Route::middleware('auth')->get('/api/opd-sales/{doctor_id}', [OpdInvoiceController::class, 'opdSales'])->name('opd.sales.api');
Route::get('/doctors/{id}/schedule', [DoctorApiController::class, 'getSchedule']);
Route::get('/services/{service}', [ServiceApiController::class, 'show'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/package-details/{package}', [PackageInvoiceController::class, 'getPackageDetails'])->name('api.package-details');
Route::middleware('auth')->prefix('service-bill-cart')->name('api.service-bill.cart.')->group(function () {
  Route::get('/', [ServiceBillController::class, 'getCartResponse'])->name('get');
  Route::post('/add', [ServiceBillController::class, 'addToCart'])->name('add');
  Route::post('/remove', [ServiceBillController::class, 'removeFromCart'])->name('remove');
});
// Route::middleware('auth')->get('/api/opd-sales/{doctor_id}', function ($doctor_id) {
//   return response()->json(['doctor_id_diterima' => $doctor_id]);
// });

Route::middleware('auth')->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/register', function () {
  if (auth()->check()) {
    return redirect()->route('register');
  }
  return view('auth.register');
});

require __DIR__ . '/auth.php';

Route::get('/api/test', function () {
  return response()->json(['message' => 'API Test Berhasil!']);
});
