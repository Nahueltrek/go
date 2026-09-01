<?php

use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\AttractionController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\BusinessController;
use App\Http\Controllers\Api\V1\ClaimController;
use App\Http\Controllers\Api\V1\DestinationController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\MapController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\RouteController;
use App\Http\Controllers\Api\V1\RoutePointController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // --- Autenticación (tokens Sanctum, no cookies de sesión) ---
    Route::post('auth/register', RegisterController::class);
    Route::post('auth/login', LoginController::class);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', LogoutController::class);
        Route::get('auth/me', MeController::class);
    });

    // --- Lectura pública ---
    Route::get('destinations/{slug}', [DestinationController::class, 'show']);

    Route::get('businesses', [BusinessController::class, 'index']);
    Route::get('businesses/{slug}', [BusinessController::class, 'show']);

    Route::get('attractions', [AttractionController::class, 'index']);
    Route::get('attractions/{slug}', [AttractionController::class, 'show']);

    Route::get('activities', [ActivityController::class, 'index']);
    Route::get('activities/{slug}', [ActivityController::class, 'show']);

    Route::get('routes', [RouteController::class, 'index']);
    Route::get('routes/{slug}', [RouteController::class, 'show']);

    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{slug}', [ArticleController::class, 'show']);

    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{slug}', [EventController::class, 'show']);

    Route::get('map/nearby', [MapController::class, 'nearby']);

    // --- Escritura (requiere token; la autorización fina la resuelve cada
    // Policy — ver App\Policies) ---
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('businesses', [BusinessController::class, 'store']);
        Route::put('businesses/{slug}', [BusinessController::class, 'update']);

        Route::post('attractions', [AttractionController::class, 'store']);
        Route::post('routes', [RouteController::class, 'store']);
        Route::post('routes/{slug}/points', [RoutePointController::class, 'store']);

        Route::post('businesses/{slug}/claim', [ClaimController::class, 'store']);
        Route::post('claims/{claim}/approve', [ClaimController::class, 'approve']);
        Route::post('claims/{claim}/reject', [ClaimController::class, 'reject']);

        Route::post('businesses/{slug}/reviews', [ReviewController::class, 'store']);

        Route::get('favorites', [FavoriteController::class, 'index']);
        Route::post('favorites/toggle', [FavoriteController::class, 'toggle']);
    });
});
