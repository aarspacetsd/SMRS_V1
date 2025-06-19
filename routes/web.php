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

// --- Controllers Modul Patient (Pages/Patient/) ---
use App\Http\Controllers\Pages\Patient\PatientTableController; // Untuk Patient Table
use App\Http\Controllers\Pages\Patient\AppointmentTableController; // Untuk Appointment Table

// --- Controllers Modul Report (Pages/Report/) ---
use App\Http\Controllers\Pages\Report\OpdReportController; // Untuk OPD Reports
use App\Http\Controllers\Pages\Report\PackageReportController; // Untuk Package Reports

// --- Controllers Modul Invoice/Bill (Pages/InvoiceBill/) ---
use App\Http\Controllers\Pages\InvoiceBill\AllReportController;
use App\Http\Controllers\Pages\InvoiceBill\ServiceReportController;
use App\Http\Controllers\Pages\InvoiceBill\TotalCollectionController;
use App\Http\Controllers\Pages\InvoiceBill\OpdInvoiceController;
use App\Http\Controllers\Pages\InvoiceBill\PackageInvoiceController;
use App\Http\Controllers\Pages\InvoiceBill\ServiceBillController;

// --- Controllers Modul Lab Test (Pages/LabTest/) ---
use App\Http\Controllers\Pages\LabTest\ExaminationTestController;
use App\Http\Controllers\Pages\LabTest\HematologiTestController;
use App\Http\Controllers\Pages\LabTest\ManageTestController;
use App\Http\Controllers\Pages\LabTest\MicrobiologyTestController;
use App\Http\Controllers\Pages\LabTest\LabReportController; // Nama 'Report' bisa bentrok, jadi pakai LabReport
use App\Http\Controllers\Pages\LabTest\StainTestController;
use App\Http\Controllers\Pages\LabTest\TestReferenceController;

// --- Controllers Modul Setting (Pages/Setting/) ---
use App\Http\Controllers\Pages\Setting\UserController; // Untuk Manage User (bukan admin/user)
use App\Http\Controllers\Pages\Setting\HospitalSettingController;
use App\Http\Controllers\Pages\Setting\RoleController; // Untuk Manage Role
use App\Models\Patient;

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
  // URL: /dashboard
  // Name: dashboard.index
  Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

  // 2. Modul Admin
  Route::prefix('admin')->group(function () {
    // URL: /dashboard/admin/departemen
    // Name: dashboard.admin.departments.index
    // Route::get('/departemen', [DepartmentController::class, 'index'])->name('dashboard.admin.departments.index');
    Route::resource('departments', DepartmentController::class);

    // URL: /dashboard/admin/doctors
    // Name: dashboard.admin.doctors.index
    // Route::get('/doctors', [DoctorController::class, 'index'])->name('dashboard.admin.doctors.index');

    Route::resource('doctors', DoctorController::class);
    // URL: /dashboard/admin/employees
    // Name: dashboard.admin.employees.index
    // Route::get('/employees', [EmployeeController::class, 'index'])->name('dashboard.admin.employees.index');
    Route::resource('employees', EmployeeController::class);
    // Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    // Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    // Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    // Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    // // Perhatikan: Rute untuk update sekarang menggunakan POST, bukan PUT/PATCH
    // Route::post('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');


    // URL: /dashboard/admin/packages
    // Name: dashboard.admin.packages.index
    Route::resource('/packages', PackageTableController::class);
    Route::post('/packages/test/delete', [PackageTableController::class, 'packageTestDelete'])->name('packages.test.delete');
    Route::post('/packages/delete', [PackageTableController::class, 'delete'])->name('packages.delete');

    // URL: /dashboard/admin/services
    // Name: dashboard.admin.services.index
    Route::resource('/services', ServiceTableController::class);
  });

  // 3. Modul Patient
  Route::prefix('patient')->group(function () {
    // URL: /dashboard/patient/table
    // Name: dashboard.patient.table.index
    Route::resource('/patients', PatientTableController::class);

    // URL: /dashboard/patient/appointments
    // Name: dashboard.patient.appointments.index
    Route::get('/appointments', [AppointmentTableController::class, 'index'])->name('dashboard.patient.appointments.index');
  });

  // 4. Modul Report
  Route::prefix('reports')->group(function () {
    // URL: /dashboard/reports/opd
    // Name: dashboard.reports.opd.index
    Route::get('/opd', [OpdReportController::class, 'index'])->name('dashboard.reports.opd.index');

    // URL: /dashboard/reports/packages
    // Name: dashboard.reports.packages.index
    Route::get('/packages', [PackageReportController::class, 'index'])->name('dashboard.reports.packages.index');
  });

  // 5. Modul Invoice/Bill
  Route::prefix('invoice-bill')->group(function () {
    // URL: /dashboard/invoice-bill/all
    // Name: dashboard.invoice-bill.all.index
    Route::get('/all', [AllReportController::class, 'index'])->name('dashboard.invoice-bill.all.index');

    // URL: /dashboard/invoice-bill/service-reports
    // Name: dashboard.invoice-bill.service-reports.index
    Route::get('/service-reports', [ServiceReportController::class, 'index'])->name('dashboard.invoice-bill.service-reports.index');

    // URL: /dashboard/invoice-bill/total-collection
    // Name: dashboard.invoice-bill.total-collection.index
    Route::get('/total-collection', [TotalCollectionController::class, 'index'])->name('dashboard.invoice-bill.total-collection.index');

    // URL: /dashboard/invoice-bill/opd-invoice
    // Name: dashboard.invoice-bill.opd-invoice.index
    Route::get('/opd-invoice', [OpdInvoiceController::class, 'index'])->name('dashboard.invoice-bill.opd-invoice.index');

    // URL: /dashboard/invoice-bill/package-invoice
    // Name: dashboard.invoice-bill.package-invoice.index
    Route::get('/package-invoice', [PackageInvoiceController::class, 'index'])->name('dashboard.invoice-bill.package-invoice.index');

    // URL: /dashboard/invoice-bill/service-bill
    // Name: dashboard.invoice-bill.service-bill.index
    Route::get('/service-bill', [ServiceBillController::class, 'index'])->name('dashboard.invoice-bill.service-bill.index');
  });

  // 6. Modul Lab Test
  Route::prefix('lab-test')->group(function () {
    // URL: /dashboard/lab-test/examination
    // Name: dashboard.lab-test.examination.index
    Route::get('/examination', [ExaminationTestController::class, 'index'])->name('dashboard.lab-test.examination.index');

    // URL: /dashboard/lab-test/hematology
    // Name: dashboard.lab-test.hematology.index
    Route::get('/hematology', [HematologiTestController::class, 'index'])->name('dashboard.lab-test.hematology.index');

    // URL: /dashboard/lab-test/manage
    // Name: dashboard.lab-test.manage.index
    Route::get('/manage', [ManageTestController::class, 'index'])->name('dashboard.lab-test.manage.index');

    // URL: /dashboard/lab-test/microbiology
    // Name: dashboard.lab-test.microbiology.index
    Route::get('/microbiology', [MicrobiologyTestController::class, 'index'])->name('dashboard.lab-test.microbiology.index');

    // URL: /dashboard/lab-test/reports
    // Name: dashboard.lab-test.reports.index
    Route::get('/reports', [LabReportController::class, 'index'])->name('dashboard.lab-test.reports.index');

    // URL: /dashboard/lab-test/stain
    // Name: dashboard.lab-test.stain.index
    Route::get('/stain', [StainTestController::class, 'index'])->name('dashboard.lab-test.stain.index');

    // URL: /dashboard/lab-test/references
    // Name: dashboard.lab-test.references.index
    Route::get('/references', [TestReferenceController::class, 'index'])->name('dashboard.lab-test.references.index');
  });

  // 7. Modul Setting
  Route::prefix('settings')->group(function () {
    // URL: /dashboard/settings/users
    // Name: dashboard.settings.users.index
    Route::get('/users', [UserController::class, 'index'])->name('dashboard.settings.users.index');

    // URL: /dashboard/settings/hospital
    // Name: dashboard.settings.hospital.index
    Route::get('/hospital', [HospitalSettingController::class, 'index'])->name('dashboard.settings.hospital.index');

    // URL: /dashboard/settings/roles
    // Name: dashboard.settings.roles.index
    Route::get('/roles', [RoleController::class, 'index'])->name('dashboard.settings.roles.index');
  });

  // --- Opsional: Rute AJAX untuk DataTables (jika menggunakan server-side) ---
  // Jika Anda punya banyak tabel AJAX, mungkin akan ada lebih banyak rute di sini.
  // Misalnya, untuk tabel Doctors:
  // Route::get('/api/doctors-data', [Admin\DoctorController::class, 'getDataForTable'])->name('dashboard.api.doctors-data');
});



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
