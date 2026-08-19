<?php

namespace App\Http\Controllers;

use App\Services\ListingPosterRenderer;
use App\Support\ListingPosterHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListingPosterController extends Controller
{
    public function export(Request $request, ListingPosterRenderer $renderer): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'layout' => 'nullable|in:premium,basic',
            'price' => 'required|string|max:32',
            'stats' => 'required|array',
            'stats.win_rate' => 'nullable|string|max:16',
            'stats.heroes_count' => 'nullable|string|max:16',
            'stats.skins_count' => 'nullable|string|max:16',
            'stats.rank' => 'nullable|string|max:32',
            'stats.level' => 'nullable|string|max:16',
            'primary_image' => 'required|string|max:15000000',
            'frames' => 'required|array',
            'featured_skins' => 'nullable|array|max:8',
            'gallery_skins' => 'required|array|max:48',
            'gallery_layout' => 'nullable|array',
            'emotes' => 'nullable|array|max:12',
            'recalls' => 'nullable|array|max:12',
            'collection_badge_url' => 'nullable|string|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid poster data.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $premium = ($request->input('layout') === 'premium')
            || ListingPosterHelper::userHasPremiumLayout($user);

        $payload = $renderer->normalizePayload($validator->validated(), $premium);

        if (! str_starts_with($payload['primary_image'], 'data:image/')) {
            return response()->json([
                'success' => false,
                'message' => 'Primary screenshot must be provided as an image.',
            ], 422);
        }

        try {
            $relativePath = $renderer->render(
                $payload,
                (int) $user->id,
                $request->getSchemeAndHttpHost()
            );
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Could not generate poster on the server. Please try again in a moment.',
            ], 500);
        }

        $downloadUrl = route('account.listing-poster.download', [
            'path' => base64_encode($relativePath),
        ]);

        return response()->json([
            'success' => true,
            'url' => Storage::disk('public')->url($relativePath),
            'download_url' => $downloadUrl,
            'filename' => 'listing-poster.png',
        ]);
    }

    public function download(string $path): BinaryFileResponse
    {
        $relative = base64_decode($path, true);
        if (! is_string($relative) || $relative === '' || str_contains($relative, '..')) {
            abort(404);
        }

        if (! str_starts_with($relative, 'listing-posters/'.Auth::id().'/')) {
            abort(403);
        }

        $full = Storage::disk('public')->path($relative);
        if (! is_file($full)) {
            abort(404);
        }

        return response()->download($full, 'listing-poster.png', [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function renderPreview(string $token, ListingPosterRenderer $renderer)
    {
        $payload = $renderer->pullPayload($token);
        if (! is_array($payload)) {
            abort(404);
        }

        $premium = ($payload['layout'] ?? 'basic') === 'premium';

        return view('listing.poster-export', [
            'poster' => $payload,
            'premium' => $premium,
            'priceConfig' => config('listing_poster.price.'.($premium ? 'premium' : 'basic')),
        ]);
    }
}
