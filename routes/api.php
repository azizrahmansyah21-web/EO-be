<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlastController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\RsvpController;
use App\Http\Controllers\Api\ScannerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Stitch - Agung Toyota Event Management API Routes (Laravel 13)
|--------------------------------------------------------------------------
*/

// =========================================================================
// 1. Authentication (Public & Protected)
// =========================================================================
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'loginAdmin']);
    Route::post('/sales-login', [AuthController::class, 'loginSales']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// =========================================================================
// 2. High-Performance Multi-Pos Scanner (Pessimistic Locking lockForUpdate)
// =========================================================================
Route::prefix('scanner')->group(function () {
    Route::post('/verify', [ScannerController::class, 'verify']);
    Route::get('/quotas', [ScannerController::class, 'getQuotaStats']);
});

// =========================================================================
// 3. Public RSVP & Client-side QR E-Ticket (Rate Limited)
// =========================================================================
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/rsvp/{token}', [RsvpController::class, 'getInvitation']);
    Route::post('/rsvp/{token}/confirm', [RsvpController::class, 'confirm']);
    Route::get('/ticket/{token}', [RsvpController::class, 'getTicket']);
});

// =========================================================================
// 4. Admin & Sales Guest Registry
// =========================================================================
Route::prefix('admin')->group(function () {
    Route::get('/guests', [GuestController::class, 'index']);
    Route::post('/guests', [GuestController::class, 'store']);
    Route::post('/guests/import', [GuestController::class, 'import']);

    // WhatsApp Blast Queue & Real-time Polling
    Route::post('/blast/start', [BlastController::class, 'start']);
    Route::get('/blast/progress', [BlastController::class, 'progress']);
});

Route::prefix('sales')->group(function () {
    Route::get('/guests', [GuestController::class, 'index']);
    Route::post('/guests', [GuestController::class, 'store']);
});
