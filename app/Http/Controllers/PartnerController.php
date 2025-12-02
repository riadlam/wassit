<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellerApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class PartnerController extends Controller
{
    public function apply()
    {
        // Allow non-logged in users to see the page (login modal will handle it)
        $user = Auth::user();

        if ($user) {
            $user->loadMissing('seller');

            // Check if user already has an approved application (is already a seller)
            if ($user->seller) {
                return redirect()->route('account.dashboard');
            }

            // Check if user has a pending or rejected application
            $application = SellerApplication::where('user_id', $user->id)->first();

            if ($application) {
                if ($application->status === 'approved') {
                    return redirect()->route('account.dashboard');
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
            $approveUrl = route('partner.application.approve', [
                'applicationId' => $application->id,
                'userId' => $user->id,
                'token' => env('ADMIN_ACTION_TOKEN', 'local-dev-token'),
            ]);
            $rejectUrl = route('partner.application.reject', [
                'applicationId' => $application->id,
                'userId' => $user->id,
                'token' => env('ADMIN_ACTION_TOKEN', 'local-dev-token'),
            ]);
            $resp = Http::post($apiUrl, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ['text' => 'Approve', 'url' => $approveUrl],
                            ['text' => 'Reject', 'url' => $rejectUrl],
                        ],
                    ],
                ],
            ]);
        } catch (\Throwable $t) {}

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully! We will review it and get back to you soon.',
        ]);
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

