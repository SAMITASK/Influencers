<?php

use App\Http\Controllers\Api\Influencer;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::prefix('influencers')->group(function () {
    Route::get('/', [Influencer::class, 'index']);
    Route::post('/', [Influencer::class, 'store']);
    Route::put('/{id}', [Influencer::class, 'update']);
    Route::delete('/{id}', [Influencer::class, 'destroy']);
});

Route::get('/cart-details', [CartController::class, 'index']);
