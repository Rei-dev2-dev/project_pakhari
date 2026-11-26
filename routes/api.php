<?php

use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\TandonMonitoringController;
use Illuminate\Support\Facades\Route;

Route::prefix('tandon')->group(function (): void {
    Route::get('/status', [TandonMonitoringController::class, 'getStatus'])->name('api.tandon.status');
    Route::get('/logs', [TandonMonitoringController::class, 'getRecentLogs'])->name('api.tandon.logs');
    Route::post('/telemetry', [TandonMonitoringController::class, 'receiveTelemetry'])->name('api.tandon.telemetry');
});

// Dedicated Multi-Tank IoT Endpoints for WT32-ETH01 (ESP32-ETH01) + JSN-SR04T Ultrasonic Sensors
Route::prefix('iot')->group(function (): void {
    Route::get('/status', [MonitoringController::class, 'getAllStatuses'])->name('api.iot.status');
    Route::post('/telemetry', [TandonMonitoringController::class, 'receiveTelemetry'])->name('api.iot.telemetry');
});
