<?php

namespace App\Services;

use App\Support\ListingPosterHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Throwable;

class ListingPosterRenderer
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function render(array $payload, int $userId, ?string $baseUrl = null): string
    {
        $relativePath = sprintf(
            'listing-posters/%d/%s.png',
            $userId,
            Str::uuid()->toString()
        );

        $absolutePath = Storage::disk('public')->path($relativePath);
        $directory = dirname($absolutePath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $width = (int) config('listing_poster.width', 681);
        $height = (int) config('listing_poster.height', 1024);
        $exportWidth = (int) config('listing_poster.export_width', 1080);
        $exportHeight = (int) round($height * ($exportWidth / max($width, 1)));

        $token = Str::uuid()->toString();
        $this->storePayload($token, $payload);

        $root = rtrim($baseUrl ?: (string) config('app.url'), '/');
        $renderUrl = $root.route('account.listing-poster.render', ['token' => $token], false);

        $shot = Browsershot::url($renderUrl)
            ->windowSize($exportWidth, $exportHeight)
            ->waitUntilNetworkIdle(false)
            ->setOption('waitUntil', 'domcontentloaded')
            ->setDelay(1500)
            ->setScreenshotType('png')
            ->dismissDialogs()
            ->timeout(120);

        $chromePath = config('listing_poster.chrome_path');
        if (is_string($chromePath) && $chromePath !== '' && is_file($chromePath)) {
            $shot->setChromePath($chromePath);
        } elseif (PHP_OS_FAMILY === 'Windows' && is_file('C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe')) {
            $shot->setChromePath('C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe');
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            $shot->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox']);
        }

        try {
            $shot->save($absolutePath);
        } catch (Throwable $exception) {
            $this->forgetPayload($token);
            Log::error('Listing poster export failed', [
                'user_id' => $userId,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $this->forgetPayload($token);

        return $relativePath;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function pullPayload(string $token): ?array
    {
        $path = $this->payloadPath($token);
        if (! is_file($path)) {
            return null;
        }

        $payload = json_decode((string) file_get_contents($path), true);
        if (! is_array($payload)) {
            return null;
        }

        if (($payload['_expires_at'] ?? 0) < time()) {
            @unlink($path);

            return null;
        }

        unset($payload['_expires_at']);

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function storePayload(string $token, array $payload): void
    {
        $directory = storage_path('app/listing-poster-cache');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $payload['_expires_at'] = time() + 600;
        file_put_contents(
            $this->payloadPath($token),
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    protected function forgetPayload(string $token): void
    {
        $path = $this->payloadPath($token);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    protected function payloadPath(string $token): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9\-]/', '', $token) ?: $token;

        return storage_path('app/listing-poster-cache/'.$safe.'.json');
    }

    /**
     * Normalize incoming client payload for rendering.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function normalizePayload(array $input, bool $premium): array
    {
        $gallerySkins = array_values($input['gallery_skins'] ?? []);
        $galleryLayout = $input['gallery_layout'] ?? null;
        if (! is_array($galleryLayout) || empty($galleryLayout['cols'])) {
            $galleryLayout = $premium
                ? ['cols' => 6, 'rows' => 1, 'count' => count($gallerySkins)]
                : ListingPosterHelper::galleryGridForCount(count($gallerySkins));
        }

        return [
            'layout' => $premium ? 'premium' : 'basic',
            'price' => (string) ($input['price'] ?? '0'),
            'stats' => (array) ($input['stats'] ?? []),
            'primary_image' => (string) ($input['primary_image'] ?? ''),
            'frames' => (array) ($input['frames'] ?? []),
            'featured_skins' => array_values($input['featured_skins'] ?? []),
            'gallery_skins' => $gallerySkins,
            'gallery_layout' => $galleryLayout,
            'emotes' => array_values($input['emotes'] ?? []),
            'recalls' => array_values($input['recalls'] ?? []),
            'collection_badge_url' => (string) ($input['collection_badge_url'] ?? ''),
            'poster_bg' => $premium ? '/images/listing-poster-bg.png' : '/images/listing-poster-bg-basic.jpg',
        ];
    }
}
