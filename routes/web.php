<?php

use Illuminate\Support\Facades\Route;
use App\Models\Partner;
use App\Http\Controllers\PartnerRegistrationController;
use App\Http\Controllers\PartnerVerificationController;

Route::get('/', function () {
    return view('welcome');
});

// Partner Registration Routes
Route::get('/partner/register/{token}', [PartnerRegistrationController::class, 'show'])
    ->name('partner.register');

Route::post('/partner/register/{token}', [PartnerRegistrationController::class, 'store'])
    ->name('partner.store');

    // web.php
Route::get('partner/success', [PartnerRegistrationController::class, 'success'])
    ->name('partner.success');
    
// Partner QR Code Verification Route
Route::get('/partner/verify/{id}', [PartnerRegistrationController::class, 'verify'])
    ->name('partner.verify');

    