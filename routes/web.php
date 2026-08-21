<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Broadcasting authentication routes (must be first, before any catch-all routes)
Broadcast::routes(['middleware' => ['web', 'auth']]);

// Language switcher route. Storing the locale marks it as an explicit choice,
// which takes priority over the language detected from the browser or phone.
Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, \App\Http\Middleware\SetLocale::supportedLocales(), true), 404);

    session(['locale' => $locale]);

    return redirect()->back();
})->name('locale.switch');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::view('/terms-of-service', 'terms-of-service')->name('terms-of-service');

Route::get('/storage/{path}', function (string $path) {
    $relative = str_replace('\\', '/', $path);
    $relative = ltrim($relative, '/');

    if ($relative === '' || str_contains($relative, '..')) {
        abort(404);
    }

    $full = storage_path('app/public/'.$relative);

    if (! is_file($full)) {
        abort(404);
    }

    return response()->file($full, [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*');

Route::get('/games/{slug}', [GameController::class, 'show'])->name('games.show');
Route::get('/apply', [PartnerController::class, 'apply'])->name('partner.apply');
Route::post('/apply', [PartnerController::class, 'submitApplication'])->name('partner.apply.submit');

// Telegram webhook for inline keyboard callbacks (no CSRF)
Route::post('/telegram/webhook', [PartnerController::class, 'telegramWebhook'])
    ->name('telegram.webhook')
    ->middleware('throttle:30,1')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

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
        Route::post('/conversations/{id}/confirm-delivery', [\App\Http\Controllers\ChatController::class, 'confirmDelivery'])->name('confirm-delivery');
    });

    Route::get('/wallet', [DashboardController::class, 'wallet'])->name('wallet');
    Route::get('/library', [DashboardController::class, 'library'])->name('library');

    // Seller-only routes
    Route::middleware(\App\Http\Middleware\EnsureUserIsSeller::class)->group(function () {
        Route::post('/wallet/withdrawals', [WithdrawalController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('wallet.withdrawals.store');
        Route::post('/catalog/emotes', [App\Http\Controllers\MlbbCatalogController::class, 'storeEmote'])
            ->middleware('throttle:20,1')
            ->name('catalog.emotes.store');
        Route::post('/catalog/recalls', [App\Http\Controllers\MlbbCatalogController::class, 'storeRecall'])
            ->middleware('throttle:20,1')
            ->name('catalog.recalls.store');
        Route::get('/listed-accounts', [DashboardController::class, 'listedAccounts'])->name('listed-accounts');
        Route::get('/listed-accounts/create', [DashboardController::class, 'createAccount'])->name('listed-accounts.create');
        Route::post('/listed-accounts', [DashboardController::class, 'storeAccount'])->name('listed-accounts.store');
        Route::get('/listed-accounts/{id}/edit', [DashboardController::class, 'editAccount'])->name('listed-accounts.edit');
        Route::put('/listed-accounts/{id}', [DashboardController::class, 'updateAccount'])->name('listed-accounts.update');
        Route::patch('/listed-accounts/{id}/status', [DashboardController::class, 'updateAccountStatus'])->name('listed-accounts.update-status');
        Route::delete('/listed-accounts/{id}', [DashboardController::class, 'deleteAccount'])->name('listed-accounts.delete');
    });

    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    Route::post('/settings/update-profile', [DashboardController::class, 'updateProfile'])->name('settings.update-profile');
    Route::post('/settings/logout-all', [DashboardController::class, 'logoutAllDevices'])->name('settings.logout-all');
    Route::delete('/settings/logout-session/{sessionId}', [DashboardController::class, 'logoutSession'])->name('settings.logout-session');
});

// Order creation route (web-based, uses session auth) - must be authenticated
Route::post('/orders/create/{account_id}', [App\Http\Controllers\OrderController::class, 'create'])->name('orders.create')->middleware('auth');

// Checkout route (must come before catch-all route)
Route::get('/checkout/{encryptedOrderId}', [App\Http\Controllers\CheckoutController::class, 'show'])->name('checkout.show');

// Payment routes
Route::post('/payment/initiate/{encryptedOrderId}', [App\Http\Controllers\PaymentController::class, 'initiatePayment'])->name('payment.initiate');
Route::get('/payment/sofizpay/cib/return', [App\Http\Controllers\PaymentController::class, 'sofizpayCibReturn'])->name('payment.sofizpay.cib.return');
Route::get('/payment/failure/{encryptedOrderId}', [App\Http\Controllers\PaymentController::class, 'paymentFailure'])->name('payment.failure');

// Webhook routes (public, no auth required)
Route::post('/webhook/baridimob', [App\Http\Controllers\WebhookController::class, 'chargilyWebhook'])->name('webhook.chargily');

// MLBB API playground (dev tool for exploring live game data)
Route::prefix('mlbb/playground')->name('mlbb.playground.')->group(function () {
    Route::get('/', [App\Http\Controllers\MlbbPlaygroundController::class, 'index'])->name('index');
    Route::get('/heroes', [App\Http\Controllers\MlbbPlaygroundController::class, 'heroes'])->name('heroes');
    Route::get('/search', [App\Http\Controllers\MlbbPlaygroundController::class, 'search'])->name('search');
    Route::get('/emotes', [App\Http\Controllers\MlbbPlaygroundController::class, 'emotes'])->name('emotes');
    Route::get('/recalls', [App\Http\Controllers\MlbbPlaygroundController::class, 'recalls'])->name('recalls');
    Route::get('/heroes/{hero}/emotes', [App\Http\Controllers\MlbbPlaygroundController::class, 'heroEmotes'])
        ->where('hero', '.+')
        ->name('hero.emotes');
    Route::get('/heroes/{hero}', [App\Http\Controllers\MlbbPlaygroundController::class, 'show'])
        ->where('hero', '.+')
        ->name('hero');
});

Route::get('/mlbb/image-proxy', function (\Illuminate\Http\Request $request) {
    if ($request->hasSession()) {
        $request->session()->save();
    }

    $url = (string) $request->query('url', '');
    if ($url === '') {
        abort(400);
    }

    if (str_starts_with($url, '/storage/')) {
        $localPath = storage_path('app/public/'.ltrim(substr($url, strlen('/storage/')), '/'));
        if (is_file($localPath)) {
            return response()->file($localPath, [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
        abort(404);
    }

    if (! filter_var($url, FILTER_VALIDATE_URL)) {
        abort(400);
    }

    $storagePath = (string) (parse_url($url, PHP_URL_PATH) ?: '');
    if (str_contains($storagePath, '/storage/')) {
        $relative = ltrim(str_after($storagePath, '/storage/'), '/');
        if ($relative !== '' && ! str_contains($relative, '..')) {
            $localPath = storage_path('app/public/'.$relative);
            if (is_file($localPath)) {
                return response()->file($localPath, [
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }
    }

    if (str_contains($url, 'Special:FilePath/')) {
        $path = (string) (parse_url($url, PHP_URL_PATH) ?: '');
        $file = urldecode(basename($path));
        if (preg_match('/^(.+?) Skin Tag\.png$/i', $file, $matches) === 1) {
            $localPath = app(\App\Services\MlbbSkinCatalogService::class)->localTagAbsolutePath($matches[1]);
            if ($localPath) {
                return response()->file($localPath, [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
            $url = app(\App\Services\MlbbFandomService::class)->resolveTagImageUrl($matches[1]);
        }
    }

    $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?: ''));
    $allowed = str_ends_with($host, 'fandom.com')
        || str_ends_with($host, 'wikia.nocookie.net')
        || str_ends_with($host, 'wikia.com')
        || str_ends_with($host, 'yuanzhanapp.com')
        || str_ends_with($host, 'mobilelegends.com');

    if (! $allowed) {
        abort(403);
    }

    $candidates = array_values(array_unique(array_filter([
        $url,
        preg_replace('/\?.*$/', '', $url),
    ])));

    $response = null;
    foreach ($candidates as $candidate) {
        $response = \Illuminate\Support\Facades\Http::timeout(20)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                'Referer' => 'https://mobile-legends.fandom.com/',
            ])
            ->get($candidate);

        if ($response->successful()) {
            break;
        }
    }

    if (! $response || $response->failed()) {
        abort(502);
    }

    return response($response->body(), 200, [
        'Content-Type' => $response->header('Content-Type') ?: 'image/png',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->middleware('auth')->name('mlbb.image-proxy');

// Account details route (catch-all must be last)
Route::get('/{slug}/accounts/{id}', [App\Http\Controllers\AccountController::class, 'show'])->name('accounts.show');
