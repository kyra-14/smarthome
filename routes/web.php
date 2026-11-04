<?php

use App\Http\Controllers\SmartHomeController;
use Illuminate\Support\Facades\Route;

// Halaman Utama Maket (Web & PWA Mobile)
Route::get('/', [SmartHomeController::class, 'index'])->name('home');

// PWA Manifest
Route::get('/manifest.json', function () {
    return response(file_get_contents(public_path('manifest.json')), 200, [
        'Content-Type' => 'application/manifest+json',
    ]);
});

// API Kontrol 6 Lampu Maket & Arduino
Route::prefix('api')->group(function () {
    Route::get('/status', [SmartHomeController::class, 'getStatus']);
    Route::get('/maket/status', [SmartHomeController::class, 'getStatus']); // Alias untuk kodingan mikrokontroler
    Route::post('/lampu/{device}/toggle', [SmartHomeController::class, 'toggleDevice']);
    Route::post('/devices/{device}/toggle', [SmartHomeController::class, 'toggleDevice']); // Alias
    Route::post('/lampu/all', [SmartHomeController::class, 'toggleAll']);
    Route::get('/telemetry', [SmartHomeController::class, 'telemetry']);
});
