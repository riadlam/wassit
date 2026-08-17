<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\SellerApplication;
use App\Services\Admin\SellerApplicationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PartnerController extends Controller
{
    public function __construct(
        protected SellerApplicationService $sellerApplicationService
    ) {
    }

    public function apply()
    {
        // Allow non-logged in users to see the page (login modal will handle it)
        $user = Auth::user();
        $games = Game::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        if ($user) {
            $user->loadMissing('seller');

            // If user is already a seller, just redirect to home for now
            if ($user->seller) {
                return redirect('/');
            }

            // Check if user has a pending or rejected application
            $application = SellerApplication::where('user_id', $user->id)->first();

            if ($application) {
                if ($application->status === 'approved') {
                    // Approved but dashboard route not defined; redirect to home
                    return redirect('/');
                }
                
                // Show under review message for pending or rejected applications
                return view('partner.apply', [
                    'hasApplication' => true,
                    'application' => $application,
                    'games' => $games,
                ]);
            }
        }

        return view('partner.apply', [
            'hasApplication' => false,
            'requiresAuth' => !$user,
            'games' => $games,
        ]);
    }

    public function submitApplication(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        // Check if user already has an application
        $existingApplication = SellerApplication::where('user_id', $user->id)->first();
        if ($existingApplication) {
            return response()->json(['error' => 'You have already submitted an application.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'business_name' => 'nullable|string|max:255',
            'experience' => 'required|string',
            'games' => 'required|array|min:1|max:20',
            'games.*' => 'required|integer|distinct|exists:games,id',
            'account_count' => 'required|string',
            'terms' => 'accepted',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $selectedGameIds = collect($validated['games'])
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();
        $gameNamesById = Game::query()
            ->whereKey($selectedGameIds)
            ->pluck('name', 'id');
        $selectedGames = $selectedGameIds
            ->map(fn (int $id): string => (string) $gameNamesById->get($id))
            ->filter()
            ->implode(', ');

        $application = SellerApplication::create([
            'user_id' => $user->id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'country' => $validated['country'],
            'business_name' => $validated['business_name'] ?? null,
            'experience' => $validated['experience'],
            'games' => $selectedGames,
            'account_count' => $validated['account_count'],
            'status' => 'pending',
        ]);

        // Send Telegram notification
        try {
            $botToken = (string) env('TELEGRAM_BOT_TOKEN', '');
            $chatId = (string) env('TELEGRAM_CHAT_ID', '');

            if ($botToken === '' || $chatId === '') {
                throw new \RuntimeException('Telegram notification credentials are not configured.');
            }

            $message = "New Seller Application\n" .
                "User ID: {$user->id}\n" .
                "Name: {$request->full_name}\n" .
                "Email: {$request->email}\n" .
                "Phone: {$request->phone}\n" .
                "Country: {$request->country}\n" .
                "Business: " . ($request->business_name ?: '-') . "\n" .
                "Experience: {$request->experience}\n" .
                "Games: {$selectedGames}\n" .
                "Accounts to List: {$request->account_count}\n" .
                "Application ID: {$application->id}";

            $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
            // Callback-only buttons (original method).
            $resp = Http::post($apiUrl, [
                'chat_id' => $chatId,
                'text' => $message,
                'disable_web_page_preview' => true,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => "✅ Approve",
                                'callback_data' => 'ap:' . $application->id,
                            ],
                            [
                                'text' => "❌ Reject",
                                'callback_data' => 'rj:' . $application->id,
                            ],
                        ],
                    ],
                ],
            ]);

            // Save telegram message id if available
            if ($resp->successful()) {
                $body = $resp->json();
                if (isset($body['result']['message_id'])) {
                    $application->telegram_message = (string)$body['result']['message_id'];
                    $application->save();
                }
            } else {
                Log::error('Telegram sendMessage: failed', [
                    'http_status' => $resp->status(),
                    'body' => $resp->body(),
                ]);
            }

            // Diagnostics: verify Telegram webhook registration/status.
            try {
                $webhookInfoUrl = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";
                $wh = Http::get($webhookInfoUrl);
                $expectedWebhookUrl = route('telegram.webhook');
                $webhookData = $wh->json('result', []);
                $currentWebhookUrl = is_array($webhookData) ? (string)($webhookData['url'] ?? '') : '';
                $configuredWebhookSecret = (string) env('TELEGRAM_WEBHOOK_SECRET', '');
                if ($configuredWebhookSecret === '') {
                    Log::error('Telegram setWebhook skipped: missing webhook secret');
                } else {
                    $setWebhookUrl = "https://api.telegram.org/bot{$botToken}/setWebhook";
                    $webhookPayload = [
                        'url' => $expectedWebhookUrl,
                        'allowed_updates' => json_encode(['callback_query']),
                        'secret_token' => $configuredWebhookSecret,
                    ];
                    $setResp = Http::post($setWebhookUrl, $webhookPayload);
                    if (!$setResp->successful()) {
                        Log::error('Telegram setWebhook failed', [
                            'http_status' => $setResp->status(),
                            'body' => $setResp->body(),
                        ]);
                    } elseif ($currentWebhookUrl !== $expectedWebhookUrl) {
                        Log::info('Telegram webhook URL updated');
                    }
                }
            } catch (\Throwable $webhookErr) {
                Log::error('Telegram getWebhookInfo exception', [
                    'message' => $webhookErr->getMessage(),
                ]);
            }
        } catch (\Throwable $t) {
            Log::error('Telegram sendMessage: exception', [
                'message' => $t->getMessage(),
                'code' => $t->getCode(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully! We will review it and get back to you soon.',
        ]);
    }

    /**
     * Telegram webhook to process inline keyboard callbacks for approve/reject.
     */
    public function telegramWebhook(Request $request)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN', '');
        $adminTelegramId = (string) env('TELEGRAM_CHAT_ID', '');
        $webhookSecret = (string) env('TELEGRAM_WEBHOOK_SECRET', '');

        if (!$botToken || !$adminTelegramId) {
            Log::error('Telegram webhook: missing security configuration');
            return response()->json(['ok' => false], 400);
        }

        if ($webhookSecret === '') {
            Log::error('Telegram webhook: missing webhook secret');
            return response()->json(['ok' => false], 503);
        }

        if (!hash_equals(
            $webhookSecret,
            (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '')
        )) {
            Log::warning('Telegram webhook: invalid secret token');
            return response()->json(['ok' => false], 403);
        }

        $update = $request->all();
        if (!isset($update['callback_query'])) {
            return response()->json(['ok' => true]);
        }

        $callback = $update['callback_query'];
        $fromId = $callback['from']['id'] ?? null;
        $message = $callback['message'] ?? null;
        $data = $callback['data'] ?? '';

        if (!hash_equals($adminTelegramId, (string) $fromId)) {
            Log::warning('Telegram webhook: unauthorized callback sender', [
                'from_id' => $fromId,
            ]);
            return response()->json(['ok' => false], 403);
        }

        // Decode callback data (supports legacy JSON and compact formats)
        $action = null;
        $applicationId = 0;
        $userId = 0;
        $parsedVia = 'unknown';

        // Try legacy JSON first
        try {
            $payload = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($payload) && ($payload['type'] ?? '') === 'application_action') {
                $action = $payload['action'] ?? null;
                $applicationId = (int)($payload['application_id'] ?? 0);
                $userId = (int)($payload['user_id'] ?? 0);
                $parsedVia = 'json';
            }
        } catch (\Throwable $t) {
            // fall through to compact parsing
        }

        if (!$action || !$applicationId) {
            // Compact format: ap:<id> or rj:<id>
            if (is_string($data) && preg_match('/^(ap|rj):(\d+)$/', $data, $m)) {
                $action = $m[1] === 'ap' ? 'approve' : 'reject';
                $applicationId = (int)$m[2];
                $parsedVia = 'compact';
            }
        }

        if (!$action || !$applicationId) {
            Log::error('Telegram webhook: unrecognized callback_data', ['data' => $data]);
            return response()->json(['ok' => false], 400);
        }

        $application = SellerApplication::find($applicationId);
        if (!$application) {
            Log::error('Telegram webhook: invalid application', [
                'application_exists' => (bool)$application,
            ]);
            return response()->json(['ok' => false, 'error' => 'Invalid application'], 400);
        }

        // Ensure we have the correct user id from the application record (compact callbacks don't include user_id)
        $userId = (int)$application->user_id;

        try {
            if ($action === 'approve') {
                $this->sellerApplicationService->approve($application);
            } elseif ($action === 'reject') {
                $this->sellerApplicationService->reject($application);
            }
            $application->refresh();
        } catch (\Throwable $t) {
            Log::error('Telegram webhook: approval/rejection exception', [
                'message' => $t->getMessage(),
            ]);
        }

        // Edit original message to reflect status
        $chatId = env('TELEGRAM_CHAT_ID', '');
        $messageId = $application->telegram_message;
        $apiEdit = "https://api.telegram.org/bot{$botToken}/editMessageText";

        // Build updated text with status
        $statusEmoji = $application->status === 'approved' ? '✅' : '❌';
        $statusLabel = ucfirst($application->status);
        $newText = (
            "New Seller Application\n" .
            "User ID: {$userId}\n" .
            "Name: {$application->full_name}\n" .
            "Email: {$application->email}\n" .
            "Phone: {$application->phone}\n" .
            "Country: {$application->country}\n" .
            "Business: " . ($application->business_name ?: '-') . "\n" .
            "Experience: {$application->experience}\n" .
            "Games: {$application->games}\n" .
            "Accounts to List: {$application->account_count}\n" .
            "Application ID: {$application->id}\n" .
            "Status: {$statusLabel} {$statusEmoji}"
        );

        try {
            if ($chatId && $messageId) {
                $editResp = Http::post($apiEdit, [
                    'chat_id' => $chatId,
                    'message_id' => (int)$messageId,
                    'text' => $newText,
                    'disable_web_page_preview' => true,
                ]);
                if (!$editResp->successful()) {
                    Log::error('Telegram webhook: editMessageText failed', [
                        'status' => $editResp->status(),
                        'body' => $editResp->body(),
                    ]);
                }
            }
        } catch (\Throwable $t) {
            Log::error('Telegram webhook: editMessageText exception', [
                'message' => $t->getMessage(),
            ]);
        }

        // Answer callback query to remove loading in Telegram client
        $apiAnswer = "https://api.telegram.org/bot{$botToken}/answerCallbackQuery";
        try {
            $ansResp = Http::post($apiAnswer, [
                'callback_query_id' => $callback['id'] ?? '',
                'text' => $action === 'approve' ? 'Approved ✅' : 'Rejected ❌',
                'show_alert' => false,
            ]);
            if (!$ansResp->successful()) {
                Log::error('Telegram webhook: answerCallbackQuery failed', [
                    'status' => $ansResp->status(),
                    'body' => $ansResp->body(),
                ]);
            }
        } catch (\Throwable $t) {
            Log::error('Telegram webhook: answerCallbackQuery exception', [
                'message' => $t->getMessage(),
            ]);
        }
        return response()->json(['ok' => true]);
    }
}

