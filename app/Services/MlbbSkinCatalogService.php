<?php

namespace App\Services;

use App\Models\MlbbSkin;
use App\Models\MlbbSkinTag;
use Illuminate\Support\Str;

class MlbbSkinCatalogService
{
    /**
     * @return array<string, mixed>|null
     */
    public function heroPayload(string $heroName): ?array
    {
        $heroName = trim($heroName);
        if ($heroName === '') {
            return null;
        }

        $slug = Str::slug($heroName);
        $lower = mb_strtolower($heroName);

        $rows = MlbbSkin::query()
            ->where(function ($query) use ($heroName, $slug, $lower) {
                $query->where('hero_slug', $slug)
                    ->orWhereRaw('LOWER(hero) = ?', [$lower])
                    ->orWhere('hero', $heroName);
            })
            ->whereNotNull('image_path')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (MlbbSkin $row) => $row->imageUrl() !== null);

        if ($rows->isEmpty()) {
            return null;
        }

        $skins = $rows->map(fn (MlbbSkin $row) => $this->skinPayload($row))->values()->all();

        return [
            'name' => $rows->first()->hero,
            'source' => 'local',
            'section' => 'Splash art',
            'skins' => $skins,
            'skins_count' => count($skins),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function sampleSkins(int $count = 8): array
    {
        $rows = MlbbSkin::query()
            ->whereNotNull('image_path')
            ->inRandomOrder()
            ->limit($count * 3)
            ->get()
            ->filter(fn (MlbbSkin $row) => $row->imageUrl() !== null)
            ->take($count);

        return $rows->map(fn (MlbbSkin $row) => $this->skinPayload($row))->values()->all();
    }

    public function localTagPublicUrl(string $tagName): ?string
    {
        $tag = $this->findTag($tagName);

        return $tag?->publicUrl();
    }

    public function localTagAbsolutePath(string $tagName): ?string
    {
        $tag = $this->findTag($tagName);
        if (! $tag?->image_path) {
            return null;
        }

        $path = storage_path('app/public/'.$tag->image_path);

        return is_file($path) ? $path : null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $tags
     * @return array<int, array{name: string, image_url: string|null}>
     */
    public function localizeTags(array $tags): array
    {
        $localized = [];

        foreach ($tags as $tag) {
            $name = trim((string) ($tag['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $localized[] = [
                'name' => $name,
                'image_url' => $this->localTagPublicUrl($name) ?: ($tag['image_url'] ?? null),
            ];
        }

        return $localized;
    }

    /**
     * @return array<string, mixed>
     */
    public function skinPayload(MlbbSkin $row): array
    {
        return [
            'id' => $row->id,
            'hero' => $row->hero,
            'name' => $row->skin,
            'rarity' => $row->rarity ?: 'Skin',
            'painted' => (bool) $row->painted,
            'image_url' => $row->imageUrl(),
            'thumbnail_url' => $row->thumbnailUrl(),
            'tags' => $this->localizeTags($row->tags ?? []),
        ];
    }

    private function findTag(string $tagName): ?MlbbSkinTag
    {
        $tagName = trim($tagName);
        if ($tagName === '') {
            return null;
        }

        $slug = Str::slug($tagName);

        return MlbbSkinTag::query()
            ->where(function ($query) use ($slug, $tagName) {
                $query->where('slug', $slug)->orWhere('name', $tagName);
            })
            ->first();
    }
}
