<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Games routes
Route::get('/games', [App\Http\Controllers\GameController::class, 'index']);
Route::get('/games/{id}/attributes', [App\Http\Controllers\GameController::class, 'getAttributes']);

// Account listing routes
Route::get('/accounts', [App\Http\Controllers\AccountController::class, 'index']);
Route::get('/accounts/{id}', [App\Http\Controllers\AccountController::class, 'show']);

// Seller routes (protected)
Route::prefix('seller')->middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [App\Http\Controllers\SellerController::class, 'getProfile']);
    Route::put('/profile', [App\Http\Controllers\SellerController::class, 'updateProfile']);
    Route::get('/accounts', [App\Http\Controllers\SellerController::class, 'getAccounts']);
    Route::post('/accounts', [App\Http\Controllers\SellerController::class, 'createAccount']);
    Route::put('/accounts/{id}', [App\Http\Controllers\SellerController::class, 'updateAccount']);
    Route::delete('/accounts/{id}', [App\Http\Controllers\SellerController::class, 'deleteAccount']);
});

// Order routes (protected)
Route::prefix('orders')->middleware('auth:sanctum')->group(function () {
    Route::post('/create/{account_id}', [App\Http\Controllers\OrderController::class, 'create']);
    Route::get('/buyer', [App\Http\Controllers\OrderController::class, 'getBuyerOrders']);
    Route::get('/seller', [App\Http\Controllers\OrderController::class, 'getSellerOrders']);
    Route::post('/{id}/confirm', [App\Http\Controllers\OrderController::class, 'confirm']);
    Route::post('/{id}/cancel', [App\Http\Controllers\OrderController::class, 'cancel']);
});

// Review routes (protected)
Route::prefix('reviews')->middleware('auth:sanctum')->group(function () {
    Route::post('/{seller_id}', [App\Http\Controllers\ReviewController::class, 'create']);
});

// Public review routes
Route::get('/seller/{seller_id}/reviews', [App\Http\Controllers\ReviewController::class, 'getSellerReviews']);

