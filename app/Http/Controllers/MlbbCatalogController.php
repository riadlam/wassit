<?php

namespace App\Http\Controllers;

use App\Models\MlbbEmote;
use App\Models\MlbbRecall;
use App\Services\MlbbCatalogSyncService;
use App\Services\WebpImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class MlbbCatalogController extends Controller
{
    public function emotes(MlbbCatalogSyncService $catalog): JsonResponse
    {
        return response()->json($catalog->listEmotes());
    }

    public function recalls(MlbbCatalogSyncService $catalog): JsonResponse
    {
        return response()->json($catalog->listRecalls());
    }

    public function sampleEmotes(Request $request): JsonResponse
    {
        $count = min(12, max(1, (int) $request->query('count', 6)));

        $emotes = MlbbEmote::query()
            ->whereNotNull('image_url')
            ->inRandomOrder()
            ->limit($count)
            ->get(['id', 'name', 'image_url', 'thumbnail_url', 'group'])
            ->map(fn (MlbbEmote $row) => [
                'id' => $row->id,
                'name' => $row->name,
                'group' => $row->group,
                'image_url' => $row->image_url,
                'thumbnail_url' => $row->thumbnail_url,
            ])
            ->values();

        return response()->json(['emotes' => $emotes]);
    }

    public function sampleRecalls(Request $request): JsonResponse
    {
        $count = min(12, max(1, (int) $request->query('count', 6)));

        $recalls = MlbbRecall::query()
            ->whereNotNull('image_url')
            ->inRandomOrder()
            ->limit($count)
            ->get(['id', 'name', 'image_url', 'thumbnail_url', 'group'])
            ->map(fn (MlbbRecall $row) => [
                'id' => $row->id,
                'name' => $row->name,
                'group' => $row->group,
                'image_url' => $row->image_url,
                'thumbnail_url' => $row->thumbnail_url,
            ])
            ->values();

        return response()->json(['recalls' => $recalls]);
    }

    public function storeEmote(Request $request, WebpImageService $webp): JsonResponse
    {
        return $this->storeCatalogItem($request, $webp, 'emote');
    }

    public function storeRecall(Request $request, WebpImageService $webp): JsonResponse
    {
        return $this->storeCatalogItem($request, $webp, 'recall');
    }

    private function storeCatalogItem(Request $request, WebpImageService $webp, string $type): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $name = trim($validated['name']);
        $directory = $type === 'emote' ? 'mlbb-emotes' : 'mlbb-recalls';

        try {
            $path = $webp->storeUploadedAsWebp($request->file('image'), $directory, 512);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not process the image. Try another PNG, JPG, or WEBP.',
            ], 422);
        }

        $publicUrl = asset('storage/'.$path);
        $group = 'Uploaded';
        $slug = $this->uniqueCatalogSlug($type, $name, $group);

        if ($type === 'emote') {
            $row = MlbbEmote::create([
                'name' => $name,
                'slug' => $slug,
                'group' => $group,
                'description' => null,
                'heroes' => [],
                'thumbnail_url' => $publicUrl,
                'image_url' => $publicUrl,
            ]);
        } else {
            $row = MlbbRecall::create([
                'name' => $name,
                'slug' => $slug,
                'group' => $group,
                'description' => null,
                'thumbnail_url' => $publicUrl,
                'image_url' => $publicUrl,
            ]);
        }

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $row->id,
                'name' => $row->name,
                'group' => $row->group,
                'image_url' => $row->image_url,
                'thumbnail_url' => $row->thumbnail_url,
            ],
        ]);
    }

    private function uniqueCatalogSlug(string $type, string $name, string $group): string
    {
        $base = Str::slug($group.'-'.$name);
        if ($base === '') {
            $base = $type;
        }
        $base .= '-'.Str::lower(Str::random(6));

        $query = $type === 'emote' ? MlbbEmote::query() : MlbbRecall::query();
        $slug = $base;
        $i = 2;
        while ($query->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
            $query = $type === 'emote' ? MlbbEmote::query() : MlbbRecall::query();
        }

        return $slug;
    }
}
