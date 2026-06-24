<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\PharmacyController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InfoController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Controllers\Api\FormSubmitController;
use App\Http\Controllers\Api\CekGonderController;

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\LiveBroadcastController;
use App\Http\Controllers\Api\PlaceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Districts
    Route::get('/districts', [DistrictController::class, 'index']);
    Route::get('/districts/{id}', [DistrictController::class, 'show']);

    // Businesses
    Route::get('/businesses', [BusinessController::class, 'index']);
    Route::get('/businesses/{id}', [BusinessController::class, 'show']);

    // Places (Historical, Nature, HotSpring etc.)
    Route::get('/places', [PlaceController::class, 'index']);
    Route::get('/places/{id}', [PlaceController::class, 'show']);

    // Pharmacies
    Route::get('/pharmacies', [PharmacyController::class, 'index']);
    Route::get('/pharmacies/duty', [PharmacyController::class, 'getDuty']);

    // Auth
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/quick-login', [AuthController::class, 'quickLogin']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/profile/upload-photo', [AuthController::class, 'uploadProfilePhoto']);

    // Info
    Route::get('/announcements', [InfoController::class, 'getAnnouncements']);
    Route::get('/events', [InfoController::class, 'getEvents']);
    Route::get('/services', [InfoController::class, 'getServices']);
    Route::get('/guide', [InfoController::class, 'getGuide']);

    // Weather
    Route::get('/weather', [WeatherController::class, 'index']);

    // Live Broadcasts
    Route::get('/live-broadcasts', [LiveBroadcastController::class, 'index']);

    // Admin Stats & Dashboard
    Route::get('/admin/stats', [AdminController::class, 'dashboard']);
    
    // Forms & Interactions (Public)
    Route::post('/cek-gonder', [CekGonderController::class, 'store']);
    Route::post('/record-visit', [FormSubmitController::class, 'recordVisit']);
    Route::post('/track-business', [FormSubmitController::class, 'trackBusiness']);
    Route::post('/track-proximity', [FormSubmitController::class, 'trackProximity']);

    // User & Profile (Protected)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::get('/profile', [AuthController::class, 'getProfile']);
        
        // Sadece Check-in işlemi yetki gerektirir
        Route::post('/check-in', [FormSubmitController::class, 'checkIn']);
    });
});
