<?php

namespace App\Services;

use App\Models\MlbbSkin;
use App\Models\MlbbSkinTag;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MlbbSkinSyncService
{
    private const PROGRESS_PATH = 'mlbb-skin-sync/progress.json';

    /**
     * @var array<int, string>
     */
    private const BADGES = [
        'Collector', 'Elite', 'Special', 'Epic', 'Legend', 'Starlight',
        'Annual Starlight', 'Prime', 'Luckybox', 'Limited', 'Basic',
        'M7', 'Neobeasts', 'Neobeast', 'V.E.N.O.M', 'Transformers',
        'Kung Fu Panda', 'Sanrio', 'Aspirants', 'Zodiac', 'Superhero',
        'Lightborn', 'Dragon Tamer', 'Champion', 'Collaboration',
    ];

    public function __construct(
        private MlbbFandomService $fandom,
        private MlbbApiService $mlbb,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        return [
            'skin_count' => MlbbSkin::count(),
            'skin_with_image' => MlbbSkin::query()->whereNotNull('image_path')->count(),
            'tag_count' => MlbbSkinTag::query()->whereNotNull('image_path')->count(),
            'hero_count' => MlbbSkin::query()->distinct()->count('hero'),
            'progress' => $this->readProgress(),
        ];
    }

    /**
     * @param  callable(string): void  $line
     * @return array{badges: int, heroes: int, skins: int}
     */
    public function sync(callable $line, bool $force = false, bool $badgesOnly = false, ?string $onlyHeroes = null): array
    {
        $progress = $this->readProgress();
        $badges = $this->syncBadges($line, $progress, $force);

        if ($badgesOnly) {
            return ['badges' => $badges, 'heroes' => 0, 'skins' => 0];
        }

        $officialNames = [];
        foreach ($this->mlbb->listHeroes() as $hero) {
            $name = trim((string) ($hero['name'] ?? ''));
            if ($name !== '') {
                $officialNames[Str::slug($name)] = $name;
            }
        }

        $heroes = $this->heroNames($officialNames, $onlyHeroes);
        $syncedHeroes = 0;
        $syncedSkins = 0;

        foreach ($heroes as $index => $wikiName) {
            $key = Str::slug($wikiName);
            $state = $progress['heroes'][$key] ?? null;
            if (! $force && ($state['status'] ?? null) === 'done') {
                $line(sprintf('[%d/%d] skip %s (already saved)', $index + 1, count($heroes), $wikiName));
                continue;
            }

            $line(sprintf('[%d/%d] %s', $index + 1, count($heroes), $wikiName));
            $progress['heroes'][$key] = ['status' => 'in_progress', 'at' => now()->toIso8601String()];
            $this->writeProgress($progress);

            try {
                $payload = $this->fandom->getHeroSkins($wikiName);
            } catch (\Throwable $exception) {
                $line('  wiki error: '.$exception->getMessage());
                $progress['heroes'][$key] = [
                    'status' => 'error',
                    'error' => $exception->getMessage(),
                    'at' => now()->toIso8601String(),
                ];
                $this->writeProgress($progress);
                sleep(2);
                continue;
            }

            $saved = $this->storeHeroSkins($wikiName, $payload['skins'] ?? [], $line, $force);
            $syncedSkins += $saved;
            $syncedHeroes++;
            $progress['heroes'][$key] = [
                'status' => $saved > 0 ? 'done' : 'empty',
                'skins' => $saved,
                'at' => now()->toIso8601String(),
            ];
            $this->writeProgress($progress);
            $line("  saved {$saved} skin images");
            usleep(800000);
        }

        return ['badges' => $badges, 'heroes' => $syncedHeroes, 'skins' => $syncedSkins];
    }

    /**
     * @param  callable(string): void  $line
     * @param  array<string, mixed>  $progress
     */
    private function syncBadges(callable $line, array &$progress, bool $force): int
    {
        $saved = 0;
        $line('Downloading rarity badges...');

        foreach (self::BADGES as $name) {
            $slug = Str::slug($name) ?: md5(mb_strtolower($name));
            $existing = MlbbSkinTag::query()->where('slug', $slug)->first();
            if (! $force && $existing?->image_path && is_file(storage_path('app/public/'.$existing->image_path))) {
                $line("  [skip] {$name}");
                continue;
            }

            $source = $this->fandom->resolveTagImageUrl($name);
            if ($source === '' || str_contains($source, '/storage/mlbb_skin_tags/')) {
                if ($existing?->image_path) {
                    $saved++;
                    continue;
                }
            }

            $relative = 'mlbb_skin_tags/'.$slug.'.png';
            $path = $this->downloadImage($source, $relative, $force);
            MlbbSkinTag::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'image_path' => $path,
                    'source_url' => $source ?: null,
                ]
            );

            $progress['badges'][$name] = $path ? 'ok' : 'missing';
            $this->writeProgress($progress);
            $line($path ? "  badge {$name}" : "  missing {$name}");
            if ($path) {
                $saved++;
            }
            usleep(350000);
        }

        $line("Badges saved: {$saved}");

        return $saved;
    }

    /**
     * @param  array<string, string>  $officialNames
     * @return array<int, string>
     */
    private function heroNames(array $officialNames, ?string $onlyHeroes): array
    {
        if ($onlyHeroes) {
            return array_values(array_filter(array_map('trim', explode(',', $onlyHeroes))));
        }

        $names = [];
        foreach (MlbbSkin::query()->select('hero')->distinct()->pluck('hero') as $hero) {
            $wiki = $officialNames[Str::slug((string) $hero)] ?? $this->titleHero((string) $hero);
            if (! in_array($wiki, $names, true)) {
                $names[] = $wiki;
            }
        }

        foreach ($officialNames as $name) {
            if (! in_array($name, $names, true)) {
                $names[] = $name;
            }
        }

        sort($names);

        return $names;
    }

    /**
     * @param  array<int, array<string, mixed>>  $skins
     * @param  callable(string): void  $line
     */
    private function storeHeroSkins(string $wikiName, array $skins, callable $line, bool $force): int
    {
        $heroSlug = Str::slug($wikiName);
        $role = MlbbSkin::query()->where('hero_slug', $heroSlug)->value('role') ?: 'Unknown';
        $saved = 0;

        foreach (array_values($skins) as $index => $skin) {
            $name = trim((string) ($skin['name'] ?? ''));
            $url = (string) ($skin['image_url'] ?? $skin['thumbnail_url'] ?? '');
            if ($name === '' || $url === '') {
                continue;
            }

            $painted = (bool) ($skin['painted'] ?? false);
            $skinSlug = Str::slug($name) ?: md5(mb_strtolower($wikiName.'-'.$name));
            if ($painted) {
                $skinSlug .= '-painted';
            }

            $relative = 'mlbb_skins/'.$heroSlug.'/'.$skinSlug.'.png';
            $path = $this->downloadImage($url, $relative, $force);
            if (! $path) {
                $line("  failed: {$name}");
                continue;
            }

            $tags = [];
            foreach ($skin['tags'] ?? [] as $tag) {
                $tagName = trim((string) ($tag['name'] ?? ''));
                if ($tagName === '') {
                    continue;
                }
                $tagImage = app(MlbbSkinCatalogService::class)->resolveTagImageUrl($tagName);
                $tags[] = [
                    'name' => $tagName,
                    'image_url' => $tagImage,
                ];
                $this->ensureBadge($tagName, (string) ($tag['image_url'] ?? ''), $force);
            }

            $row = MlbbSkin::query()
                ->where('hero_slug', $heroSlug)
                ->where(function ($query) use ($name, $skinSlug, $painted) {
                    $query->where('skin_slug', $skinSlug);
                    if (! $painted) {
                        $query->orWhere(function ($inner) use ($name) {
                            $inner->whereRaw('LOWER(skin) = ?', [mb_strtolower($name)])
                                ->where('painted', false);
                        });
                    }
                })
                ->first();

            $payload = [
                'role' => $row?->role ?: $role,
                'hero' => $row?->hero ?: $wikiName,
                'skin' => $name,
                'role_slug' => Str::slug($row?->role ?: $role),
                'hero_slug' => $heroSlug,
                'skin_slug' => $row?->skin_slug ?: $skinSlug,
                'sort_order' => $index,
                'rarity' => $skin['rarity'] ?? null,
                'painted' => $painted,
                'image_path' => $path,
                'thumbnail_path' => $path,
                'source_image_url' => $url,
                'tags' => $tags,
                'synced_at' => now(),
            ];

            if ($row) {
                $row->fill($payload);
                $row->save();
            } else {
                MlbbSkin::create($payload);
            }

            $saved++;
        }

        return $saved;
    }

    private function ensureBadge(string $name, string $sourceUrl, bool $force): void
    {
        if (strcasecmp($name, 'Painted') === 0) {
            return;
        }

        $slug = Str::slug($name) ?: md5(mb_strtolower($name));
        $existing = MlbbSkinTag::query()->where('slug', $slug)->first();
        if (! $force && $existing?->image_path && is_file(storage_path('app/public/'.$existing->image_path))) {
            return;
        }

        $source = $sourceUrl !== '' ? $sourceUrl : $this->fandom->resolveTagImageUrl($name);
        $relative = 'mlbb_skin_tags/'.$slug.'.png';
        $path = $this->downloadImage($source, $relative, $force);

        MlbbSkinTag::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'image_path' => $path,
                'source_url' => $source ?: null,
            ]
        );
    }

    private function downloadImage(string $url, string $relativePath, bool $force): ?string
    {
        $full = storage_path('app/public/'.$relativePath);
        File::ensureDirectoryExists(dirname($full));

        if (! $force && is_file($full) && filesize($full) > 64) {
            return $relativePath;
        }

        if ($url === '' || str_starts_with($url, '/storage/')) {
            return is_file($full) ? $relativePath : null;
        }

        if (str_starts_with($url, asset('storage/'))) {
            return is_file($full) ? $relativePath : null;
        }

        try {
            $response = Http::timeout(40)
                ->retry(2, 400)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                    'Referer' => 'https://mobile-legends.fandom.com/',
                ])
                ->get($url);
        } catch (\Throwable) {
            return is_file($full) ? $relativePath : null;
        }

        if (! $response->successful() || strlen($response->body()) < 64) {
            return is_file($full) ? $relativePath : null;
        }

        File::put($full, $response->body());

        return $relativePath;
    }

    private function titleHero(string $name): string
    {
        $name = trim($name);
        $aliases = [
            'change' => "Chang'e",
            'lapu-lapu' => 'Lapu-Lapu',
            'lapu lapu' => 'Lapu-Lapu',
            'yi-sun-shin' => 'Yi Sun-shin',
            'yi sun shin' => 'Yi Sun-shin',
            'gatotkaca' => 'Gatotkaca',
            'khofra' => 'Khufra',
        ];

        return $aliases[Str::slug($name)] ?? $aliases[mb_strtolower($name)] ?? Str::title($name);
    }

    /**
     * @return array<string, mixed>
     */
    private function readProgress(): array
    {
        $path = storage_path('app/'.self::PROGRESS_PATH);
        if (! is_file($path)) {
            return ['badges' => [], 'heroes' => []];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : ['badges' => [], 'heroes' => []];
    }

    /**
     * @param  array<string, mixed>  $progress
     */
    private function writeProgress(array $progress): void
    {
        $path = storage_path('app/'.self::PROGRESS_PATH);
        File::ensureDirectoryExists(dirname($path));
        $progress['updated_at'] = now()->toIso8601String();
        File::put($path, json_encode($progress, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
