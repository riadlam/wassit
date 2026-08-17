<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Resolve the locale for this request, preferring an explicit choice made
     * through the language switcher and otherwise negotiating the language
     * reported by the visitor's browser or phone.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = self::supportedLocales();
        $chosen = $request->hasSession() ? $request->session()->get('locale') : null;

        $locale = in_array($chosen, $supported, true)
            ? $chosen
            : $request->getPreferredLanguage($supported);

        if (! in_array($locale, $supported, true)) {
            $locale = $supported[0];
        }

        App::setLocale($locale);

        $response = $next($request);

        // Responses now differ by the visitor's language, so shared caches must
        // not reuse one language's HTML for another.
        $response->headers->set('Vary', 'Accept-Language', false);

        return $response;
    }

    /**
     * Supported locales with the application default first, which is also the
     * fallback used when the visitor's languages match none of them.
     *
     * @return list<string>
     */
    public static function supportedLocales(): array
    {
        $locales = array_keys(config('app.available_locales', []));

        if ($locales === []) {
            $locales = ['en'];
        }

        $default = (string) config('app.locale', 'en');

        if (in_array($default, $locales, true)) {
            $locales = array_merge([$default], array_values(array_diff($locales, [$default])));
        }

        return array_values($locales);
    }
}
