<?php

use App\Http\Controllers\CredentialsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentEnrollmentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\SecretaryManagementController;
use App\Http\Controllers\SectorLeaderController;
use App\Http\Controllers\SpendingCapController;
use App\Http\Controllers\StudentAnalysisController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard principal - redireciona para o dashboard apropriado com base no papel do usuário
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('permission:view dashboard')
    ->name('dashboard');

// Rotas específicas para cada tipo de dashboard
Route::get('/mayor-dashboard', [DashboardController::class, 'mayorDashboard'])
    ->middleware(['permission:view dashboard', 'role:mayor'])
    ->name('mayor.dashboard');

Route::get('/secretary-dashboard', [DashboardController::class, 'secretaryDashboard'])
    ->middleware(['permission:view dashboard', 'role:education_secretary|secretary'])
    ->name('secretary.dashboard');


Route::get('/sector-dashboard', [DashboardController::class, 'sectorLeaderDashboard'])
    ->middleware(['permission:view dashboard', 'role:sector_leader'])
    ->name('sector.dashboard');

Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
Route::post('/profile/remove-avatar', [ProfileController::class, 'removeAvatar'])->name('profile.remove.avatar');

Route::get('/secretaries/manage', [SecretaryManagementController::class, 'index'])
    ->middleware('permission:manage secretaries')
    ->name('secretaries.manage');

Route::post('/secretaries/associate', [SecretaryManagementController::class, 'associate'])
    ->middleware('permission:manage secretaries')
    ->name('secretaries.associate');

Route::delete('/secretaries/remove/{userId}', [SecretaryManagementController::class, 'removeAssociation'])
    ->middleware('permission:manage secretaries')
    ->name('secretaries.remove');

Route::get('/sector-leaders', [SectorLeaderController::class, 'index'])
    ->middleware('permission:manage users')
    ->name('sector-leaders.index');

Route::post('/sector-leaders', [SectorLeaderController::class, 'store'])
    ->middleware('permission:manage users')
    ->name('sector-leaders.store');

Route::delete('/sector-leaders/{userId}', [SectorLeaderController::class, 'destroy'])
    ->middleware('permission:manage users')
    ->name('sector-leaders.destroy');

Route::get('/credentials', [CredentialsController::class, 'index'])
    ->middleware('permission:manage users|manage secretaries')
    ->name('credentials.index');

Route::middleware(['permission:manage users'])->group(function () {
    Route::get('/secretary/sector-leaders', [SecretaryController::class, 'sectorLeaderAssignment'])
        ->name('secretary.sector-leaders');
    Route::post('/assign-leader', [SecretaryController::class, 'assignLeader'])
        ->name('secretary.assign-leader');
    Route::delete('/remove-leader/{id}', [SecretaryController::class, 'removeLeader'])
        ->name('secretary.remove-leader');
});

Route::middleware(['permission:manage departments'])->group(function () {
    Route::get('/secretary/departments', [SecretaryController::class, 'departments'])
        ->name('secretary.departments');
    Route::post('/secretary/departments', [SecretaryController::class, 'storeDepartment'])
        ->name('secretary.departments.store');
    Route::delete('/secretary/departments/{id}', [SecretaryController::class, 'deleteDepartment'])
        ->name('secretary.departments.delete');
});

Route::resource('expense-types', ExpenseTypeController::class)
    ->middleware('permission:manage departments');
Route::resource('expenses', ExpenseController::class)
    ->middleware('permission:manage departments');

Route::get('/dashboard/filter', [DashboardController::class, 'filter'])
    ->middleware('permission:view dashboard')
    ->name('dashboard.filter');
Route::get('/dashboard/secretary/{secretary}/details', [DashboardController::class, 'getSecretaryDetails'])
    ->middleware('permission:view dashboard')
    ->name('dashboard.secretary.details');

Route::middleware(['permission:view financial dashboard'])->group(function () {
    Route::resource('spending-caps', SpendingCapController::class);
});

Route::get('/cantina/report', [\App\Http\Controllers\CantinaReportController::class, 'showMonthCost'])
    ->middleware('permission:view cantina report')
    ->name('cantina.report');

Route::get('/reports/students', [StudentAnalysisController::class, 'index'])
    ->middleware('permission:view all schools')
    ->name('reports.students');

Route::middleware(['permission:manage students'])->group(function () {
    Route::get('/enrollments/create', [DepartmentEnrollmentController::class, 'create'])
        ->name('enrollments.create');
    Route::post('/enrollments', [DepartmentEnrollmentController::class, 'store'])
        ->name('enrollments.store');
    Route::get('/enrollments/{enrollment}/edit', [DepartmentEnrollmentController::class, 'edit'])
        ->name('enrollments.edit');
    Route::put('/enrollments/{enrollment}', [DepartmentEnrollmentController::class, 'update'])
        ->name('enrollments.update');
    Route::delete('/enrollments/{enrollment}', [DepartmentEnrollmentController::class, 'destroy'])
        ->name('enrollments.destroy');
});


require __DIR__ . '/auth.php';
