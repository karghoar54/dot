<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Versioned API routes (v1)
Route::prefix('v1')->group(function () {
    // Authentication endpoints
    Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);

    Route::middleware(['auth:sanctum', 'localization'])->prefix('dots')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\DotController::class, 'index']);
        Route::get('/{dotnumber}', [\App\Http\Controllers\Api\DotController::class, 'show']);
    });

    // Onboarding endpoints
    Route::post('/kargho/onboard-from-fmcsa/{dotnumber}', [\App\Http\Controllers\Api\KarghoOnboardingController::class, 'onboardFromFMCSA']);
    // Offboarding endpoint (now DELETE)
    Route::delete('/kargho/offboard-from-fmcsa/{dotnumber}', [\App\Http\Controllers\Api\KarghoOnboardingController::class, 'offboardFromFMCSA']);
});

// Optionally, you can deprecate or remove the unversioned routes below
// Route::post('/login', ...);
// ...existing code...
