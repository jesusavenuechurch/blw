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
    