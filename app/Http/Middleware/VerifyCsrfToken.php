<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'broadcasting/*',
        'broadcasting/auth',
        'webhook/baridimob',
    ];
    
    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        // Always exclude broadcasting routes - check this first
        if ($request->is('broadcasting/*') || $request->path() === 'broadcasting/auth') {
            \Log::info('CSRF: Broadcasting route excluded', [
                'path' => $request->path(),
                'uri' => $request->getRequestUri(),
            ]);
            return true;
        }
        
        // Fall back to parent method
        return parent::inExceptArray($request);
    }
}
