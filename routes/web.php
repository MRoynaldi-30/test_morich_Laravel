<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SewingLineController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\ProductionScheduleController;
use App\Http\Controllers\DailyProductionController;

Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::view('/sewing-lines', 'sewing-lines.index')
    ->name('sewing-lines.page');

Route::view('/orders', 'orders.index')
    ->name('orders.page');

Route::view('/production-schedules', 'schedules.index')
    ->name('schedules.page');

// Schedule
Route::get('/schedules', [
    ProductionScheduleController::class,
    'index',
])->name('schedules.index');

Route::post('/schedules', [
    ProductionScheduleController::class,
    'store',
])->name('schedules.store');

Route::get('/schedules/{id}', [
    ProductionScheduleController::class,
    'show',
])->whereNumber('id')->name('schedules.show');

// Detail produksi harian
Route::get('/schedules/{scheduleId}/daily-productions', [
    DailyProductionController::class,
    'index',
])->whereNumber('scheduleId')->name('daily-productions.index');

Route::post('/daily-productions/{dailyProductionId}/actual', [
    DailyProductionController::class,
    'store',
])->whereNumber('dailyProductionId')->name('daily-productions.actual');

Route::get('/api/sewing-lines', [
    \App\Http\Controllers\SewingLineController::class, 'index'
]);

Route::post('/api/sewing-lines', [
    \App\Http\Controllers\SewingLineController::class, 'store'
]);

Route::put('/api/sewing-lines/{id}', [
    \App\Http\Controllers\SewingLineController::class, 'update'
])->whereNumber('id');

Route::get('/api/orders', [
    \App\Http\Controllers\ProductionOrderController::class, 'index'
]);

Route::post('/api/orders', [
    \App\Http\Controllers\ProductionOrderController::class, 'store'
]);

Route::put('/api/orders/{id}', [
    \App\Http\Controllers\ProductionOrderController::class, 'update'
])->whereNumber('id');