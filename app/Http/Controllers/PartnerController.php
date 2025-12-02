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

        SellerApplication::create([
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
                "Accounts to List: {$request->account_count}";

            $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
            $resp = Http::post($apiUrl, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);
        } catch (\Throwable $t) {}

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully! We will review it and get back to you soon.',
        ]);
    }
}

