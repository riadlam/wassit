<?php

namespace App\Console\Commands;

use App\Models\MlbbSkin;
use App\Models\MlbbSkinTag;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportMlbbSkinImages extends Command
{
    protected $signature = 'mlbb:import-skin-images {--catalog= : Path to catalog.json from the Python downloader}';

    protected $description = 'Import locally downloaded MLBB skin and badge images into the database';

    public function handle(): int
    {
        $path = (string) ($this->option('catalog') ?: storage_path('app/mlbb-skin-sync/catalog.json'));

        if (! is_file($path)) {
            $this->error("Catalog not found: {$path}");
            $this->line('Run the Python downloader first: python scripts/download_mlbb_skins.py');

            return self::FAILURE;
        }

        $payload = json_decode((string) file_get_contents($path), true);
        if (! is_array($payload)) {
            $this->error('Catalog JSON is invalid.');

            return self::FAILURE;
        }

        $tagCount = 0;
        foreach ($payload['tags'] ?? [] as $tag) {
            $name = trim((string) ($tag['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            MlbbSkinTag::updateOrCreate(
                ['slug' => Str::slug($name) ?: md5(mb_strtolower($name))],
                [
                    'name' => $name,
                    'image_path' => $tag['image_path'] ?? null,
                    'source_url' => $tag['source_url'] ?? null,
                ]
            );
            $tagCount++;
        }

        $skinCount = 0;
        foreach ($payload['skins'] ?? [] as $skin) {
            $hero = trim((string) ($skin['hero'] ?? ''));
            $name = trim((string) ($skin['name'] ?? ''));
            if ($hero === '' || $name === '') {
                continue;
            }

            $heroSlug = Str::slug($hero);
            $skinSlug = Str::slug($name) ?: md5(mb_strtolower($hero.'-'.$name));
            if (! empty($skin['painted'])) {
                $skinSlug .= '-painted';
            }

            MlbbSkin::updateOrCreate(
                ['hero_slug' => $heroSlug, 'skin_slug' => $skinSlug],
                [
                    'role' => $skin['role'] ?? 'Unknown',
                    'hero' => $hero,
                    'skin' => $name,
                    'role_slug' => Str::slug((string) ($skin['role'] ?? 'unknown')),
                    'sort_order' => (int) ($skin['sort_order'] ?? 0),
                    'rarity' => $skin['rarity'] ?? null,
                    'painted' => (bool) ($skin['painted'] ?? false),
                    'image_path' => $skin['image_path'] ?? null,
                    'thumbnail_path' => $skin['thumbnail_path'] ?? null,
                    'source_image_url' => $skin['source_image_url'] ?? null,
                    'tags' => $skin['tags'] ?? [],
                    'synced_at' => now(),
                ]
            );
            $skinCount++;
        }

        $this->info("Imported {$tagCount} badges and {$skinCount} skins.");

        return self::SUCCESS;
    }
}
