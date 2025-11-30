<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSeller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'You must be logged in to access this page.');
        }

        // Check if user is a seller
        if (Auth::user()->role !== 'seller') {
            return redirect()->route('account.index')->with('error', 'You must be a seller to access this page.');
        }

        return $next($request);
    }
}
