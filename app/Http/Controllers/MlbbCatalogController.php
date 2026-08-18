<?php

namespace App\Http\Controllers;

use App\Models\MlbbEmote;
use App\Models\MlbbRecall;
use App\Services\MlbbCatalogSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
