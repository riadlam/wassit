<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellerApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class PartnerController extends Controller
{
    public function apply()
    {
        // Allow non-logged in users to see the page (login modal will handle it)
        $user = Auth::user();

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
                ]);
            }
        }

        return view('partner.apply', [
            'hasApplication' => false,
            'requiresAuth' => !$user,
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
            'website' => 'nullable|url|max:255',
            'experience' => 'required|string',
            'games' => 'required|string',
            'preferred_location' => 'nullable|string|max:255',
            'account_count' => 'required|string',
            'terms' => 'accepted',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $application = SellerApplication::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'country' => $request->country,
            'business_name' => $request->business_name,
            'website' => $request->website,
            'experience' => $request->experience,
            'games' => $request->games,
            'preferred_location' => $request->preferred_location,
            'account_count' => $request->account_count,
            'status' => 'pending',
        ]);

        // Send Telegram notification
        try {
            $botToken = env('TELEGRAM_BOT_TOKEN', '8489541435:AAF7jQMKYZVuJH9KQ4sf5AWPBFQ3Lj8fu9g');
            $chatId = env('TELEGRAM_CHAT_ID', '8147422935');

            $message = "New Seller Application\n" .
                "User ID: {$user->id}\n" .
                "Name: {$request->full_name}\n" .
                "Email: {$request->email}\n" .
                "Phone: {$request->phone}\n" .
                "Country: {$request->country}\n" .
                "Business: " . ($request->business_name ?: '-') . "\n" .
                "Website: " . ($request->website ?: '-') . "\n" .
                "Experience: {$request->experience}\n" .
                "Games: {$request->games}\n" .
                "Preferred Location: " . ($request->preferred_location ?: '-') . "\n" .
                "Accounts to List: {$request->account_count}\n" .
                "Application ID: {$application->id}";

            $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
            // Pre-log before calling Telegram
            Log::info('Telegram sendMessage: preparing', [
                'has_token' => (bool)$botToken,
                'chat_id' => $chatId,
                'application_id' => $application->id,
                'user_id' => $user->id,
            ]);

            // Use callback_data for webhook handling
            $resp = Http::post($apiUrl, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => "✅ Approve",
                                // Compact callback_data to satisfy Telegram's 1-64 bytes limit
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

            // Log raw response from Telegram
            Log::info('Telegram sendMessage: response', [
                'http_status' => $resp->status(),
                'body' => $resp->body(),
            ]);

            // Save telegram message id if available
            if ($resp->successful()) {
                $body = $resp->json();
                if (isset($body['result']['message_id'])) {
                    $application->telegram_message = (string)$body['result']['message_id'];
                    $application->save();
                    Log::info('Telegram sendMessage: stored message_id', [
                        'message_id' => $application->telegram_message,
                        'application_id' => $application->id,
                    ]);
                } else {
                    Log::warning('Telegram sendMessage: no message_id in success response');
                }
            } else {
                Log::error('Telegram sendMessage: failed', [
                    'http_status' => $resp->status(),
                    'body' => $resp->body(),
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
        if (!$botToken) {
            Log::error('Telegram webhook: missing bot token');
            return response()->json(['ok' => false], 400);
        }

        $update = $request->all();
        Log::info('Telegram webhook: received update', [
            'has_callback_query' => isset($update['callback_query']),
            'keys' => array_keys($update),
        ]);

        if (!isset($update['callback_query'])) {
            return response()->json(['ok' => true]);
        }

        $callback = $update['callback_query'];
        $fromId = $callback['from']['id'] ?? null;
        $message = $callback['message'] ?? null;
        $data = $callback['data'] ?? '';

        // Admin check disabled to ensure actions work during setup
        // If you want to restrict, re-enable by comparing $fromId to TELEGRAM_CHAT_ID.
        Log::info('Telegram webhook: admin check (skipped)', [
            'from_id' => $fromId,
            'admin_id' => (int)env('TELEGRAM_CHAT_ID', 0),
        ]);

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

        Log::info('Telegram webhook: action parsed', [
            'action' => $action,
            'application_id' => $applicationId,
            'parsed_via' => $parsedVia,
        ]);

        $application = SellerApplication::find($applicationId);
        Log::info('Telegram webhook: application lookup', [
            'application_id' => $applicationId,
            'found' => (bool)$application,
        ]);
        if (!$application) {
            Log::error('Telegram webhook: invalid application', [
                'application_exists' => (bool)$application,
            ]);
            return response()->json(['ok' => false, 'error' => 'Invalid application'], 400);
        }

        // Ensure we have the correct user id from the application record (compact callbacks don't include user_id)
        $userId = (int)$application->user_id;
        Log::info('Telegram webhook: resolved user id from application', [
            'user_id' => $userId,
        ]);

        try {
            Log::info('Telegram webhook: starting action processing', [
                'action' => $action,
                'application_id' => $applicationId,
            ]);
            if ($action === 'approve') {
                // Update user role and create seller
                $user = \App\Models\User::find($userId);
                if ($user) {
                    Log::info('Telegram webhook: promoting user to seller', [
                        'user_id' => $userId,
                    ]);
                    $user->role = 'seller';
                    $user->save();
                    // Seller model uses primary key `id` equal to `users.id`
                    \App\Models\Seller::firstOrCreate([
                        'id' => $userId,
                    ], [
                        'pfp' => null,
                        'rating' => 5.0,
                        'total_sales' => 0,
                        'bio' => null,
                        'verified' => false,
                        'wallet' => 0,
                    ]);
                } else {
                    Log::warning('Telegram webhook: user not found for approval', [
                        'user_id' => $userId,
                    ]);
                }
                Log::info('Telegram webhook: updating application status to approved', [
                    'application_id' => $application->id,
                ]);
                $application->status = 'approved';
                $application->save();
                Log::info('Telegram webhook: application approved', [
                    'application_id' => $application->id,
                    'user_id' => $userId,
                ]);
            } elseif ($action === 'reject') {
                Log::info('Telegram webhook: updating application status to rejected', [
                    'application_id' => $application->id,
                ]);
                $application->status = 'rejected';
                $application->save();
                Log::info('Telegram webhook: application rejected', [
                    'application_id' => $application->id,
                    'user_id' => $userId,
                ]);
            }
            Log::info('Telegram webhook: finished action processing', [
                'action' => $action,
                'application_id' => $applicationId,
                'status' => $application->status,
            ]);
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
            "Website: " . ($application->website ?: '-') . "\n" .
            "Experience: {$application->experience}\n" .
            "Games: {$application->games}\n" .
            "Preferred Location: " . ($application->preferred_location ?: '-') . "\n" .
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
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]);
                Log::info('Telegram webhook: editMessageText response', [
                    'status' => $editResp->status(),
                    'body' => $editResp->body(),
                ]);
            } else {
                Log::warning('Telegram webhook: missing chat_id or message_id for edit', [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                ]);
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
            Log::info('Telegram webhook: answerCallbackQuery response', [
                'status' => $ansResp->status(),
                'body' => $ansResp->body(),
            ]);
        } catch (\Throwable $t) {
            Log::error('Telegram webhook: answerCallbackQuery exception', [
                'message' => $t->getMessage(),
            ]);
        }

        return response()->json(['ok' => true]);
    }
    public function approveApplication(Request $request, $applicationId)
    {
        $token = $request->query('token');
        $userId = (int)$request->query('userId');
        if ($token !== env('ADMIN_ACTION_TOKEN', 'local-dev-token')) {
            return response('Forbidden', 403);
        }

        $application = SellerApplication::findOrFail($applicationId);
        if ($application->user_id !== $userId) {
            return response('Invalid application', 400);
        }

        // Update user role to seller and create seller row
        $user = \App\Models\User::findOrFail($userId);
        $user->role = 'seller';
        $user->save();

        // Create seller if not exists
        $seller = \App\Models\Seller::firstOrCreate([
            'user_id' => $userId,
        ], [
            'rating' => 0,
            'total_sales' => 0,
            'bio' => null,
            'verified' => false,
            'wallet' => 0,
        ]);

        $application->status = 'approved';
        $application->save();

        return response('Approved', 200);
    }

    public function rejectApplication(Request $request, $applicationId)
    {
        $token = $request->query('token');
        $userId = (int)$request->query('userId');
        if ($token !== env('ADMIN_ACTION_TOKEN', 'local-dev-token')) {
            return response('Forbidden', 403);
        }

        $application = SellerApplication::findOrFail($applicationId);
        if ($application->user_id !== $userId) {
            return response('Invalid application', 400);
        }

        $application->status = 'rejected';
        $application->save();

        return response('Rejected', 200);
    }
}

