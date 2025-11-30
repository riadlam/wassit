<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Trim email to remove any whitespace and convert to lowercase
        $email = strtolower(trim($request->input('email', '')));

        $validator = Validator::make([
            'email' => $email,
            'password' => $request->input('password', ''),
            'username' => $request->input('username', ''),
        ], [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'username' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->username ?: $email,
            'email' => $email,
            'password' => Hash::make($request->password),
            'role' => 'buyer',
        ]);

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        // Trim email to remove any whitespace and convert to lowercase for consistency
        $email = strtolower(trim($request->input('email', '')));
        $password = $request->input('password', '');

        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
        ], [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by email (case-insensitive)
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        // Login the user
        $remember = $request->boolean('remember', false);
        Auth::login($user, $remember);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully',
            'user' => Auth::user()
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user already exists
            $user = User::where('email', $googleUser->email)->first();
            
            if ($user) {
                // User exists, log them in
                Auth::login($user);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->name ?? $googleUser->email,
                    'email' => $googleUser->email,
                    'password' => Hash::make(uniqid()), // Random password since they're using OAuth
                    'role' => 'buyer',
                    'email_verified_at' => now(), // Google emails are verified
                ]);
                
                Auth::login($user);
            }
            
            return redirect()->route('home');
        } catch (\Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Unable to login with Google. Please try again.');
        }
    }
}
