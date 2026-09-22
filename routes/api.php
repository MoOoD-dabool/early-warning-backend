<?php

use App\Http\Controllers\Api\Admin\AdminAlertController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminCityController;
use App\Http\Controllers\Api\Admin\AdminDisasterTypeController;
use App\Http\Controllers\Api\Admin\AdminEarthquakeEventController;
use App\Http\Controllers\Api\Admin\AdminFeltReportController;
use App\Http\Controllers\Api\Admin\AdminReliefRequestController;
use App\Http\Controllers\Api\Admin\AdminReliefTeamController;
use App\Http\Controllers\Api\Admin\AdminReportController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\DisasterTypeController;
use App\Http\Controllers\Api\EarthquakeEventController;
use App\Http\Controllers\Api\FeltReportController;
use App\Http\Controllers\Api\HomeSummaryController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReliefRequestController;
use App\Http\Controllers\Api\ReliefTeamController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\WeatherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile app (Flutter) API
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // --- Public ---------------------------------------------------------
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:otp');
    Route::post('resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:otp');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('auth/google', [AuthController::class, 'googleAuth'])->middleware('throttle:login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset');
    Route::post('verify-reset-otp', [AuthController::class, 'verifyResetOtp'])->middleware('throttle:otp');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:password-reset');

    Route::get('cities', [CityController::class, 'index']);
    Route::get('disaster-types', [DisasterTypeController::class, 'index']);
    Route::get('disaster-types/{disasterType}', [DisasterTypeController::class, 'show']);
    Route::get('earthquake-events', [EarthquakeEventController::class, 'index']);
    Route::get('earthquake-events/{earthquakeEvent}', [EarthquakeEventController::class, 'show']);
    Route::get('weather', [WeatherController::class, 'index']);
    Route::get('weather/forecast/{cityCode}', [WeatherController::class, 'forecast']);

    // --- Authenticated mobile user (Sanctum) -----------------------------
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('profile', [ProfileController::class, 'show']);
        Route::post('profile', [ProfileController::class, 'update']); // POST so multipart (image) works
        Route::post('profile/change-password', [ProfileController::class, 'changePassword']);

        Route::get('alerts', [AlertController::class, 'index']);
        Route::get('alerts/{alert}', [AlertController::class, 'show']);
        Route::get('my-alerts', [AlertController::class, 'myAlerts']);
        Route::get('home-summary', [HomeSummaryController::class, 'index']);

        Route::get('weather/mine', [WeatherController::class, 'mine']);

        Route::post('device-tokens', [DeviceTokenController::class, 'store']);
        Route::delete('device-tokens', [DeviceTokenController::class, 'destroy']);

        Route::get('reports', [ReportController::class, 'index']);
        Route::post('reports', [ReportController::class, 'store']);
        Route::get('reports/{report}', [ReportController::class, 'show']);

        Route::get('relief-teams', [ReliefTeamController::class, 'index']);
        Route::post('relief-requests', [ReliefRequestController::class, 'store']);

        Route::get('felt-reports/current-earthquake', [FeltReportController::class, 'currentEarthquake']);
        Route::post('felt-reports', [FeltReportController::class, 'store']);
    });

    // --- Admin (Sanctum + EnsureAdmin) -----------------------------------
    Route::prefix('admin')->group(function () {
        Route::post('login', [AdminAuthController::class, 'login'])->middleware('throttle:admin-login');

        Route::middleware(['auth:sanctum', 'admin'])->group(function () {
            Route::post('logout', [AdminAuthController::class, 'logout']);

            Route::apiResource('cities', AdminCityController::class)->except(['show']);
            Route::apiResource('disaster-types', AdminDisasterTypeController::class)->except(['show']);
            Route::apiResource('alerts', AdminAlertController::class)->except(['show']);

            Route::get('earthquake-events', [AdminEarthquakeEventController::class, 'index']);
            Route::post('earthquake-events', [AdminEarthquakeEventController::class, 'store']);
            Route::get('earthquake-events/{earthquakeEvent}', [AdminEarthquakeEventController::class, 'show']);

            Route::get('reports', [AdminReportController::class, 'index']);
            Route::get('reports/{report}', [AdminReportController::class, 'show']);
            Route::put('reports/{report}/reply', [AdminReportController::class, 'reply']);

            Route::apiResource('relief-teams', AdminReliefTeamController::class)->except(['show']);
            Route::get('relief-requests', [AdminReliefRequestController::class, 'index']);
            Route::get('relief-requests/{reliefRequest}', [AdminReliefRequestController::class, 'show']);

            Route::get('felt-reports', [AdminFeltReportController::class, 'index']);
            Route::get('felt-reports/{feltReport}', [AdminFeltReportController::class, 'show']);

            Route::get('users', [AdminUserController::class, 'index']);
            Route::get('users/{user}', [AdminUserController::class, 'show']);
        });
    });
});