<?php

use App\Http\Controllers\TandonMonitoringController;
use Illuminate\Support\Facades\Route;

Route::prefix('tandon')->group(function (): void {
    Route::get('/status', [TandonMonitoringController::class, 'getStatus'])->name('api.tandon.status');
    Route::get('/logs', [TandonMonitoringController::class, 'getRecentLogs'])->name('api.tandon.logs');
    Route::post('/telemetry', [TandonMonitoringController::class, 'receiveTelemetry'])->name('api.tandon.telemetry');
});
