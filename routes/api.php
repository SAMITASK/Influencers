<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Influencer;
use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ========================================
// 🔓 RUTAS PÚBLICAS (sin autenticación)
// ========================================
Route::post('/auth/check-phone', [AuthController::class, 'checkPhoneNumber']);
Route::post('/auth/verify-token', [AuthController::class, 'verifyToken']);

// Opcional: si quieres mantener compatibilidad con el método antiguo
// Route::post('/auth/firebase-login', [AuthController::class, 'firebaseLogin']);

// ========================================
// 🔐 RUTAS PROTEGIDAS (requieren autenticación)
// ========================================
Route::group(['middleware' => ['auth:sanctum']], function () {
    
    // Obtener usuario autenticado
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user()
        ]);
    });

    // Influencers
    Route::prefix('influencers')->group(function () {
        Route::get('/', [Influencer::class, 'index']);
        Route::post('/', [Influencer::class, 'store']);
        Route::put('/{id}', [Influencer::class, 'update']);
        Route::delete('/{id}', [Influencer::class, 'destroy']);
    });

    // Cart
    Route::get('/cart-details', [CartController::class, 'index']);
    Route::get('/cart-details/chart', [CartController::class, 'chartData']);

    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
