<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\AuthController;

// Broadcasting authentication routes (must be first, before any catch-all routes)
Broadcast::routes(['middleware' => ['web', 'auth']]);

// Language switcher route
Route::get('/locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar', 'fr'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('locale.switch');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/games/{slug}', [GameController::class, 'show'])->name('games.show');
Route::get('/apply', [PartnerController::class, 'apply'])->name('partner.apply');

// Authentication Routes
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Google OAuth Routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Dashboard Routes (must come before catch-all route) - Auth Protected
Route::prefix('account')->name('account.')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/orders', [DashboardController::class, 'orders'])->name('orders');
    Route::get('/chat', [DashboardController::class, 'chat'])->name('chat');
    
    // Chat API routes
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/conversations', [\App\Http\Controllers\ChatController::class, 'getConversations'])->name('conversations');
        Route::get('/conversations/{id}/messages', [\App\Http\Controllers\ChatController::class, 'getMessages'])->name('messages');
        Route::get('/find-conversation', [\App\Http\Controllers\ChatController::class, 'findConversation'])->name('find');
        Route::post('/conversations', [\App\Http\Controllers\ChatController::class, 'createConversation'])->name('create');
        Route::post('/conversations/{id}/messages', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('send');
    });
    
    Route::get('/wallet', [DashboardController::class, 'wallet'])->name('wallet');
    Route::get('/library', [DashboardController::class, 'library'])->name('library');
    
    // Seller-only routes
    Route::middleware(\App\Http\Middleware\EnsureUserIsSeller::class)->group(function () {
        Route::get('/listed-accounts', [DashboardController::class, 'listedAccounts'])->name('listed-accounts');
        Route::get('/listed-accounts/create', [DashboardController::class, 'createAccount'])->name('listed-accounts.create');
        Route::post('/listed-accounts', [DashboardController::class, 'storeAccount'])->name('listed-accounts.store');
        Route::get('/listed-accounts/{id}/edit', [DashboardController::class, 'editAccount'])->name('listed-accounts.edit');
        Route::put('/listed-accounts/{id}', [DashboardController::class, 'updateAccount'])->name('listed-accounts.update');
        Route::patch('/listed-accounts/{id}/status', [DashboardController::class, 'updateAccountStatus'])->name('listed-accounts.update-status');
        Route::delete('/listed-accounts/{id}', [DashboardController::class, 'deleteAccount'])->name('listed-accounts.delete');
    });
    
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
});

// Order creation route (web-based, uses session auth) - must be authenticated
Route::post('/orders/create/{account_id}', [App\Http\Controllers\OrderController::class, 'create'])->name('orders.create')->middleware('auth');

// Checkout route (must come before catch-all route)
Route::get('/checkout/{encryptedOrderId}', [App\Http\Controllers\CheckoutController::class, 'show'])->name('checkout.show');

// Payment routes
Route::post('/payment/initiate/{encryptedOrderId}', [App\Http\Controllers\PaymentController::class, 'initiatePayment'])->name('payment.initiate');
Route::get('/payment/success/{encryptedOrderId}', [App\Http\Controllers\PaymentController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/failure/{encryptedOrderId}', [App\Http\Controllers\PaymentController::class, 'paymentFailure'])->name('payment.failure');

// Webhook routes (public, no auth required)
Route::post('/webhook/baridimob', [App\Http\Controllers\WebhookController::class, 'chargilyWebhook'])->name('webhook.chargily');

// Account details route (catch-all must be last)
Route::get('/{slug}/accounts/{id}', [App\Http\Controllers\AccountController::class, 'show'])->name('accounts.show');
