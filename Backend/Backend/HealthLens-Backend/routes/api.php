<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SpecializationController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\SurgeryAnalysisController;
use App\Http\Controllers\Api\SurgeryReportController;


Route::get('__ping', function () {
    return response()->json([
        'ok' => true,
        'uri' => request()->getRequestUri(),
        'path' => request()->path(),
    ]);
});

Route::middleware(['throttle:api'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public (no auth)
    |--------------------------------------------------------------------------
    */

    // Browse
    Route::get('/specializations', [SpecializationController::class, 'index']);
    Route::get('/doctors', [DoctorController::class, 'index']);
    Route::get('/doctors/cities', [DoctorController::class, 'getCities']);
    Route::get('/doctors/areas', [DoctorController::class, 'getAreas']);
    Route::get('/doctors/{id}', [DoctorController::class, 'show']);

    // Auth (public)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);
    });

    /*
    |--------------------------------------------------------------------------
    | Protected (Sanctum)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // Auth (protected)
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me',      [AuthController::class, 'me']);
        Route::delete('/auth/account', [AuthController::class, 'deleteAccount']);

        // User profile
        Route::put('/user/profile', [UserController::class, 'updateProfile']);
        Route::post('/user/avatar', [UserController::class, 'uploadAvatar']);
        Route::post('/user/change-password', [UserController::class, 'changePassword']);

        /*
        |--------------------------------------------------------------------------
        | Chats & Messages 
        |--------------------------------------------------------------------------
        | These MUST NOT be under /auth prefix
        */

        Route::get('/chats', [ChatController::class, 'index']);
        Route::post('/chats', [ChatController::class, 'store']);
        Route::delete('/chats/all', [ChatController::class, 'clearAll']);
        Route::delete('/chats/{chat}', [ChatController::class, 'destroy']);

        Route::get('/chats/{chat}/messages', [MessageController::class, 'index']);
        Route::post('/chats/{chat}/messages', [MessageController::class, 'store']);

        /*
        |--------------------------------------------------------------------------
        | Surgery Analyses
        |--------------------------------------------------------------------------
        */

        Route::get('/surgery-analyses', [SurgeryAnalysisController::class, 'index']);
        Route::post('/surgery-analyses', [SurgeryAnalysisController::class, 'store']);
        Route::get('/surgery-analyses/{analysis}', [SurgeryAnalysisController::class, 'show']);

        /*
        |--------------------------------------------------------------------------
        | Surgery Reports (Final AI Results - History)
        |--------------------------------------------------------------------------
        */

        Route::get('/surgery-reports', [SurgeryReportController::class, 'index']);
        Route::post('/surgery-reports', [SurgeryReportController::class, 'store']);
        Route::get('/surgery-reports/{id}', [SurgeryReportController::class, 'show']);
    });
});
