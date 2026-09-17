<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/products', [DashboardController::class, 'store'])->name('dashboard.store');
    Route::get('/products/edit', [DashboardController::class, 'edit'])->name('dashboard.edit');
    Route::patch('/products/{product_id}', [DashboardController::class, 'update'])->name('dashboard.update');
    Route::patch('/products/{product_id}/status', [DashboardController::class, 'status'])->name('dashboard.status');
    Route::delete('/products/{product_id}', [DashboardController::class, 'delete'])->name('dashboard.delete');
});
