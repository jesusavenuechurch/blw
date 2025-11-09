<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PartnerRegistrationController;
use App\Http\Controllers\PartnerVerificationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/partner/download', [PartnerVerificationController::class, 'downloadPartners']);
Route::get('/partner/verify/{id}', [PartnerVerificationController::class, 'verifyPartner']);
Route::get('/partner/sync-status', [PartnerVerificationController::class, 'syncStatus']);
    