<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PartnerRegistrationController;
use App\Http\Controllers\PartnerVerificationController;
use App\Http\Controllers\CheckInController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/partner/download', [PartnerVerificationController::class, 'downloadPartners']);
Route::get('/partner/verify/{id}', [PartnerVerificationController::class, 'verifyPartner']);
Route::get('/partner/sync-status', [PartnerVerificationController::class, 'syncStatus']);
Route::post('/checkin/bulk', [CheckInController::class, 'bulkCheckIn']);
Route::post('/checkin', [CheckInController::class, 'checkIn']);
Route::get('/checkins', [CheckInController::class, 'index']);
Route::get('/checkins/stats', [CheckInController::class, 'stats']);
Route::get('/checkins/status/{partnerId}', [CheckInController::class, 'checkStatus']);
    
// Lounge scanner app, v1 (see App\Http\Controllers\Api\ScannerController)
Route::prefix('scanner/v1')->group(function () {
    Route::post('/pair', [\App\Http\Controllers\Api\ScannerController::class, 'pair'])->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureScannerDevice::class, 'throttle:240,1'])->group(function () {
        Route::get('/me', [\App\Http\Controllers\Api\ScannerController::class, 'me']);
        Route::get('/partners', [\App\Http\Controllers\Api\ScannerController::class, 'partners']);
        Route::get('/partners/{partner}/photo', \App\Http\Controllers\PartnerPhotoController::class);
        Route::get('/lookup', [\App\Http\Controllers\Api\ScannerController::class, 'lookup']);
        Route::get('/search', [\App\Http\Controllers\Api\ScannerController::class, 'search']);
        Route::post('/checkins', [\App\Http\Controllers\Api\ScannerController::class, 'pushCheckins']);
        Route::get('/checkins', [\App\Http\Controllers\Api\ScannerController::class, 'pullCheckins']);
    });
});
