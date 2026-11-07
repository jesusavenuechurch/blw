<?php

use App\Http\Controllers\PartnerRegistrationController;
use App\Http\Controllers\Workspace;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

// Partner registration (public)
Route::get('/partner/register/{token}', [PartnerRegistrationController::class, 'show'])->name('partner.register');
Route::post('/partner/register/{token}', [PartnerRegistrationController::class, 'store'])->name('partner.store');
Route::get('partner/success', [PartnerRegistrationController::class, 'success'])->name('partner.success');

// A partner's own photo, via a signed link from their success page
Route::get('/partner/photo/{partner}', App\Http\Controllers\PartnerPhotoController::class)
    ->middleware('signed')
    ->name('partner.photo');

// Partner QR code verification (public, encoded in every QR code)
Route::get('/partner/verify/{id}', [PartnerRegistrationController::class, 'verify'])->name('partner.verify');

// Staff sign-in
Route::middleware('guest')->group(function () {
    Route::get('/login', [Workspace\AuthController::class, 'show'])->name('login');
    Route::post('/login', [Workspace\AuthController::class, 'login'])->middleware('throttle:10,1');
});
Route::post('/logout', [Workspace\AuthController::class, 'logout'])->middleware('auth')->name('logout');

// The old Filament panel lived at /admin
Route::redirect('/admin', '/workspace');

// Workspace (staff only)
Route::middleware('auth')->prefix('workspace')->name('workspace.')->group(function () {
    Route::get('/', Workspace\HomeController::class)->name('home');
    Route::get('/search', Workspace\SearchController::class)->name('search');

    Route::get('/people', [Workspace\PeopleController::class, 'index'])->name('people');
    Route::post('/people', [Workspace\PeopleController::class, 'store'])->name('people.store');
    Route::post('/people/bulk', [Workspace\PeopleController::class, 'bulk'])->name('people.bulk');
    Route::put('/people/{partner}', [Workspace\PeopleController::class, 'update'])->name('people.update');
    Route::delete('/people/{partner}', [Workspace\PeopleController::class, 'destroy'])->name('people.destroy');
    Route::post('/people/{partner}/resend', [Workspace\PeopleController::class, 'resend'])->name('people.resend');
    Route::post('/people/{partner}/regenerate', [Workspace\PeopleController::class, 'regenerate'])->name('people.regenerate');
    Route::post('/people/{partner}/qr', [Workspace\PeopleController::class, 'qr'])->name('people.qr');
    Route::get('/people/{partner}/photo', App\Http\Controllers\PartnerPhotoController::class)->name('people.photo');
    Route::post('/people/{partner}/photo', [Workspace\PeopleController::class, 'uploadPhoto'])->name('people.photo.store');
    Route::delete('/people/{partner}/photo', [Workspace\PeopleController::class, 'removePhoto'])->name('people.photo.destroy');

    Route::get('/outreach', [Workspace\OutreachController::class, 'index'])->name('outreach');
    Route::post('/outreach', [Workspace\OutreachController::class, 'send'])->name('outreach.send');

    Route::get('/check-ins', [Workspace\CheckinsController::class, 'index'])->name('checkins');
    Route::get('/check-ins/report', [Workspace\CheckinsController::class, 'report'])->name('checkins.report');

    Route::get('/import', [Workspace\ImportController::class, 'index'])->name('import');
    Route::post('/import', [Workspace\ImportController::class, 'store'])->name('import.store');

    Route::get('/scanners', [Workspace\ScannersController::class, 'index'])->name('scanners');
    Route::post('/scanners', [Workspace\ScannersController::class, 'store'])->name('scanners.store');
    Route::post('/scanners/{device}/renew', [Workspace\ScannersController::class, 'renew'])->name('scanners.renew');
    Route::delete('/scanners/{device}', [Workspace\ScannersController::class, 'destroy'])->name('scanners.destroy');

    Route::get('/team', [Workspace\TeamController::class, 'index'])->name('team');
    Route::post('/team', [Workspace\TeamController::class, 'store'])->name('team.store');
    Route::put('/team/{user}', [Workspace\TeamController::class, 'update'])->name('team.update');
    Route::delete('/team/{user}', [Workspace\TeamController::class, 'destroy'])->name('team.destroy');
});
