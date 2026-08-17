<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocaleDetectionTest extends TestCase
{
    public function test_browser_languages_select_a_supported_locale(): void
    {
        $this->assertSame('ar', $this->resolveLocale('ar-DZ,ar;q=0.9,en;q=0.5'));
        $this->assertSame('fr', $this->resolveLocale('fr-FR,fr;q=0.9'));
        $this->assertSame('en', $this->resolveLocale('en-GB,en;q=0.8'));
    }

    public function test_lower_priority_supported_language_wins_over_unsupported_one(): void
    {
        $this->assertSame('fr', $this->resolveLocale('de-DE,de;q=0.9,fr;q=0.4'));
    }

    public function test_unsupported_or_missing_languages_fall_back_to_the_default(): void
    {
        $this->assertSame('en', $this->resolveLocale('de-DE,de;q=0.9'));
        $this->assertSame('en', $this->resolveLocale(null));
    }

    public function test_explicit_switcher_choice_overrides_browser_language(): void
    {
        $this->assertSame('fr', $this->resolveLocale('ar-DZ,ar;q=0.9', 'fr'));
    }

    public function test_invalid_session_locale_is_ignored(): void
    {
        $this->assertSame('ar', $this->resolveLocale('ar-DZ,ar;q=0.9', 'zz'));
    }

    public function test_response_varies_on_accept_language(): void
    {
        $request = Request::create('/');
        $request->headers->set('Accept-Language', 'ar-DZ');

        $response = (new SetLocale())->handle($request, fn (): Response => new Response('ok'));

        $this->assertSame('Accept-Language', $response->headers->get('Vary'));
    }

    private function resolveLocale(?string $acceptLanguage, ?string $sessionLocale = null): string
    {
        $request = Request::create('/');

        if ($acceptLanguage !== null) {
            $request->headers->set('Accept-Language', $acceptLanguage);
        }

        if ($sessionLocale !== null) {
            $session = new Store('locale-test', new ArraySessionHandler(120));
            $session->put('locale', $sessionLocale);
            $request->setLaravelSession($session);
        }

        (new SetLocale())->handle($request, fn (): Response => new Response('ok'));

        return App::getLocale();
    }
}
