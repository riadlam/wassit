<?php

namespace Database\Seeders;

use App\Services\MlbbSkinSyncService;
use Illuminate\Database\Seeder;

/**
 * Sync skins for heroes missing from the official Moonton hero list API
 * (currently Suyou, Lukas, Kalea, Zhuxin).
 *
 * Run on the server after deploy:
 *   php artisan db:seed --class=MlbbMissingHeroesSkinSeeder
 *
 * Or sync only Suyou:
 *   php artisan mlbb:sync-skins --heroes=Suyou --force
 */
class MlbbMissingHeroesSkinSeeder extends Seeder
{
    public function run(): void
    {
        $heroes = collect(config('mlbb.supplemental_heroes', []))
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        if ($heroes->isEmpty()) {
            $this->command?->warn('No supplemental heroes configured in config/mlbb.php.');

            return;
        }

        $this->command?->info('Syncing missing MLBB heroes: '.$heroes->implode(', '));

        /** @var MlbbSkinSyncService $sync */
        $sync = app(MlbbSkinSyncService::class);

        $result = $sync->sync(
            fn (string $line) => $this->command?->line($line),
            force: true,
            badgesOnly: false,
            onlyHeroes: $heroes->implode(','),
        );

        $this->command?->info(sprintf(
            'Missing-heroes sync done. Heroes: %d | Skin images: %d',
            $result['heroes'],
            $result['skins']
        ));
    }
}
