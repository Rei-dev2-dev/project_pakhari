<?php

use App\Http\Controllers\Admin\TankController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SuperAdmin\SidebarMenuController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\TandonMonitoringController;
use Illuminate\Support\Facades\Route;

// Default Entry Point
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('monitoring.index')
        : redirect()->route('login');
})->name('home');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes (Staff, Admin, SuperAdmin)
Route::middleware(['auth'])->group(function (): void {
    // Monitoring Routes
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/{tank}', [MonitoringController::class, 'show'])->name('monitoring.show');
    Route::post('/monitoring/{tank}/level', [MonitoringController::class, 'updateLevel'])->name('monitoring.update');
    Route::post('/monitoring/{tank}/record-bbm', [MonitoringController::class, 'recordBbm'])->name('monitoring.recordBbm');
    Route::get('/monitoring/{tank}/status', [MonitoringController::class, 'getStatus'])->name('monitoring.status');

    // Report & Export Routes
    Route::get('/laporan', [ReportController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [ReportController::class, 'export'])->name('laporan.export');

    // Chart & Analytics Routes (Admin & Superadmin)
    Route::middleware(['role:admin,superadmin'])->group(function (): void {
        Route::get('/chart', [ChartController::class, 'index'])->name('chart.index');
    });

    // Admin Routes (Admin & Superadmin)
    Route::middleware(['role:admin,superadmin'])->prefix('admin')->name('admin.')->group(function (): void {
        Route::resource('tanks', TankController::class)->names('tanks');
    });

    // SuperAdmin Routes (Superadmin only)
    Route::middleware(['role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function (): void {
        Route::resource('users', UserController::class)->names('users');
        Route::resource('menus', SidebarMenuController::class)->names('menus');
        Route::post('/menus/{menu}/toggle', [SidebarMenuController::class, 'toggle'])->name('menus.toggle');
    });
});

// Legacy backward-compatibility routes for test/simulation
Route::get('/tandon', [TandonMonitoringController::class, 'index'])->name('tandon.index');
Route::post('/tandon/level', [TandonMonitoringController::class, 'updateLevel'])->name('tandon.update');
