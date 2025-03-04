<?php

use App\Http\Controllers\CredentialsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\SecretaryManagementController;
use App\Http\Controllers\SectorLeaderController;
use App\Http\Controllers\SpendingCapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/remove-avatar', [ProfileController::class, 'removeAvatar'])->name('profile.remove.avatar');

    Route::get('/secretaries/manage', [SecretaryManagementController::class, 'index'])
        ->name('secretaries.manage');

    Route::post('/secretaries/associate', [SecretaryManagementController::class, 'associate'])
        ->name('secretaries.associate');

    Route::delete('/secretaries/remove/{userId}', [SecretaryManagementController::class, 'removeAssociation'])
        ->name('secretaries.remove');

    Route::get('/sector-leaders', [SectorLeaderController::class, 'index'])
        ->name('sector-leaders.index');

    Route::post('/sector-leaders', [SectorLeaderController::class, 'store'])
        ->name('sector-leaders.store');

    Route::delete('/sector-leaders/{userId}', [SectorLeaderController::class, 'destroy'])
        ->name('sector-leaders.destroy');

    // routes/web.php (adicionar dentro do grupo auth)
    Route::get('/credentials', [CredentialsController::class, 'index'])->name('credentials.index');

    // routes/web.php
    Route::middleware(['auth', 'role:secretary'])->group(function () {
        Route::get('/sector-leaders', [SecretaryController::class, 'sectorLeaderAssignment'])
            ->name('secretary.sector-leaders');
        Route::post('/assign-leader', [SecretaryController::class, 'assignLeader'])
            ->name('secretary.assign-leader');
        Route::delete('/remove-leader/{id}', [SecretaryController::class, 'removeLeader'])
            ->name('secretary.remove-leader');
    });

    // routes/web.php - adicione dentro do grupo de rotas autenticadas
    Route::middleware(['auth', 'role:secretary'])->group(function () {
        Route::get('/departments', [SecretaryController::class, 'departments'])
            ->name('secretary.departments');
        Route::post('/departments', [SecretaryController::class, 'storeDepartment'])
            ->name('secretary.departments.store');
        Route::delete('/departments/{id}', [SecretaryController::class, 'deleteDepartment'])
            ->name('secretary.departments.delete');
    });

    Route::resource('expense-types', ExpenseTypeController::class)->middleware(['auth', 'role:secretary|sector_leader']);
    Route::resource('expenses', ExpenseController::class)->middleware(['auth', 'role:secretary|sector_leader']);

    Route::get('/dashboard/filter', [DashboardController::class, 'filter'])->name('dashboard.filter');
    Route::get('/dashboard/secretary/{secretary}/details', [DashboardController::class, 'getSecretaryDetails'])
        ->name('dashboard.secretary.details');

    Route::middleware(['role:mayor'])->group(function () {
        Route::resource('spending-caps', SpendingCapController::class);
    });


});

require __DIR__ . '/auth.php';
