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
    public function redirectToGoogle(Request $request)
    {
        $destination = $this->resolveRedirectDestination(
            $request,
            $request->query('redirect') ?: url()->previous()
        );

        $request->session()->put('url.intended', $destination);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback(Request $request)
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

            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        } catch (\Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage());

            $destination = $request->session()->pull('url.intended', route('home'));

            return redirect()->to($destination)->with('error', 'Unable to login with Google. Please try again.');
        }
    }

    /**
     * Only allow redirects within this website.
     */
    private function resolveRedirectDestination(Request $request, ?string $destination): string
    {
        if (!$destination) {
            return route('home');
        }

        if (
            str_starts_with($destination, '/')
            && !str_starts_with($destination, '//')
            && !str_contains($destination, '\\')
        ) {
            return $destination;
        }

        $target = parse_url($destination);

        if ($target === false || empty($target['host']) || strcasecmp($target['host'], $request->getHost()) !== 0) {
            return route('home');
        }

        $path = $target['path'] ?? '/';

        if (str_contains($path, '\\')) {
            return route('home');
        }

        $query = isset($target['query']) ? '?' . $target['query'] : '';
        $fragment = isset($target['fragment']) ? '#' . $target['fragment'] : '';

        return $path . $query . $fragment;
    }
}
