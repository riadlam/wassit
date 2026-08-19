<?php

namespace App\Services;

use App\Models\MlbbSkin;
use App\Models\MlbbSkinTag;
use Illuminate\Support\Str;

class MlbbSkinCatalogService
{
    /**
     * @var array<string, array<int, string>>
     */
    private const TAG_SLUG_ALIASES = [
        'annual starlight' => ['starlight', 'annual-starlight'],
        'starlight' => ['starlight'],
        'neobeast' => ['neobeast', 'neobeasts'],
        'neobeasts' => ['neobeast', 'neobeasts'],
        'm7' => ['m7'],
        'venom' => ['venom', 'v-e-n-o-m'],
        'v.e.n.o.m' => ['venom', 'v-e-n-o-m'],
        'the aspirants' => ['the-aspirants', 'aspirants'],
        'aspirants' => ['the-aspirants', 'aspirants'],
        'kung fu panda' => ['kung-fu-panda'],
        'attack on titan' => ['attack-on-titan'],
        'naruto shippuden' => ['naruto-shippuden'],
        'jujutsu kaisen' => ['jujutsu-kaisen'],
        'star wars' => ['star-wars'],
        'saint seiya' => ['saint-seiya'],
        'double 11' => ['double-11'],
        'golden month' => ['golden-month'],
        'lunar festival' => ['lunar-festival-tag', 'lunar-festival'],
        'm world' => ['m-world'],
        'lucky box' => ['luckybox'],
        'luckybox' => ['luckybox'],
    ];

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
     * @param  array<string, mixed>  $hero
     * @return array<string, mixed>
     */
    public function enrichHeroPayload(array $hero): array
    {
        $hero['skins'] = array_map(function (array $skin) {
            $skin['tags'] = $this->resolveSkinTags(
                $skin['tags'] ?? [],
                $skin['rarity'] ?? null,
                ! empty($skin['painted'])
            );

            return $skin;
        }, $hero['skins'] ?? []);

        return $hero;
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
        return $this->resolveTagImageUrl($tagName);
    }

    public function localTagAbsolutePath(string $tagName): ?string
    {
        $url = $this->resolveTagImageUrl($tagName);
        if (! $url || ! str_starts_with($url, '/storage/')) {
            return null;
        }

        $path = storage_path('app/public/'.ltrim(substr($url, strlen('/storage/')), '/'));

        return is_file($path) ? $path : null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $tags
     * @return array<int, array{name: string, image_url: string|null}>
     */
    public function resolveSkinTags(array $tags, ?string $rarity = null, bool $painted = false): array
    {
        $resolved = [];
        $seen = [];

        foreach ($tags as $tag) {
            $name = trim((string) ($tag['name'] ?? ''));
            if ($name === '' || strcasecmp($name, 'Painted') === 0) {
                continue;
            }

            $key = mb_strtolower($name);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $resolved[] = [
                'name' => $name,
                'image_url' => $this->resolveTagImageUrl($name) ?: ($tag['image_url'] ?? null),
            ];
        }

        $rarity = trim((string) ($rarity ?? ''));
        if ($rarity !== '' && ! $painted && ! isset($seen[mb_strtolower($rarity)])) {
            $imageUrl = $this->resolveTagImageUrl($rarity);
            if ($imageUrl) {
                $resolved[] = [
                    'name' => $rarity,
                    'image_url' => $imageUrl,
                ];
            }
        }

        return array_values(array_filter(
            $resolved,
            fn (array $tag) => ! empty($tag['image_url'])
        ));
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
            'tags' => $this->resolveSkinTags($row->tags ?? [], $row->rarity, (bool) $row->painted),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function skinsByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return [];
        }

        $results = [];

        foreach (MlbbSkin::query()->whereIn('id', $ids)->orderBy('id')->get() as $row) {
            if ($row->imageUrl() !== null) {
                $results[] = $this->skinPayload($row);
                continue;
            }

            $payload = $this->skinPayload($row);
            $heroPayload = $this->heroPayload($row->hero);
            $target = mb_strtolower(trim($row->skin));
            $match = null;

            foreach (($heroPayload['skins'] ?? []) as $skin) {
                if (mb_strtolower(trim((string) ($skin['name'] ?? ''))) === $target) {
                    $match = $skin;
                    break;
                }
            }

            if (! $match) {
                $match = $this->findFandomSkinMatch($row->hero, $row->skin);
            }

            if ($match) {
                $payload['image_url'] = $match['image_url'] ?? $match['thumbnail_url'] ?? null;
                $payload['thumbnail_url'] = $match['thumbnail_url'] ?? $payload['image_url'];
                $payload['rarity'] = $payload['rarity'] ?: ($match['rarity'] ?? 'Skin');
                $payload['tags'] = $payload['tags'] ?: ($match['tags'] ?? []);
                $payload['painted'] = $payload['painted'] || ! empty($match['painted']);
            }

            if (! empty($payload['image_url']) || ! empty($payload['thumbnail_url'])) {
                $results[] = $payload;
            }
        }

        return $results;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findFandomSkinMatch(string $hero, string $skinName): ?array
    {
        try {
            $heroPayload = app(MlbbFandomService::class)->getHeroSkins($hero);
            $target = mb_strtolower(trim($skinName));
            foreach (($heroPayload['skins'] ?? []) as $skin) {
                if (mb_strtolower(trim((string) ($skin['name'] ?? ''))) === $target) {
                    return $skin;
                }
            }
        } catch (\Throwable) {
            // ignore and fall back to client-side hero catalog fetch
        }

        return null;
    }

    public function resolveTagImageUrl(string $tagName): ?string
    {
        $tagName = trim($tagName);
        if ($tagName === '' || strcasecmp($tagName, 'Painted') === 0) {
            return null;
        }

        if (str_starts_with($tagName, '/storage/mlbb_skin_tags/')) {
            return $tagName;
        }

        $record = $this->findTag($tagName);
        if ($url = $record?->publicUrl()) {
            return $url;
        }

        foreach ($this->tagSlugCandidates($tagName) as $slug) {
            $relative = 'mlbb_skin_tags/'.$slug.'.png';
            if (is_file(storage_path('app/public/'.$relative))) {
                return '/storage/'.$relative;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public function tagSlugCandidates(string $tagName): array
    {
        $lower = mb_strtolower(trim($tagName));
        $candidates = [Str::slug($tagName)];

        if (isset(self::TAG_SLUG_ALIASES[$lower])) {
            $candidates = array_merge($candidates, self::TAG_SLUG_ALIASES[$lower]);
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    private function findTag(string $tagName): ?MlbbSkinTag
    {
        $tagName = trim($tagName);
        if ($tagName === '') {
            return null;
        }

        foreach ($this->tagSlugCandidates($tagName) as $slug) {
            $tag = MlbbSkinTag::query()->where('slug', $slug)->first();
            if ($tag) {
                return $tag;
            }
        }

        return MlbbSkinTag::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($tagName)])
            ->first();
    }
}
