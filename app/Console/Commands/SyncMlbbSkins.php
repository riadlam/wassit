<?php

namespace App\Console\Commands;

use App\Services\MlbbSkinSyncService;
use Illuminate\Console\Command;

class SyncMlbbSkins extends Command
{
    protected $signature = 'mlbb:sync-skins
        {--badges-only : Download rarity badges only}
        {--heroes= : Comma-separated hero names}
        {--force : Re-download files that already exist}';

    protected $description = 'Download MLBB skin images and rarity badges into local storage and the skins table';

    public function handle(MlbbSkinSyncService $sync): int
    {
        set_time_limit(0);

        $this->info('Saving skin images and badges locally. This can be re-run if it stops.');

        $result = $sync->sync(
            fn (string $line) => $this->line($line),
            (bool) $this->option('force'),
            (bool) $this->option('badges-only'),
            $this->option('heroes') ? (string) $this->option('heroes') : null,
        );

        $this->newLine();
        $this->info(sprintf(
            'Done. Badges: %d | Heroes updated: %d | Skin images: %d',
            $result['badges'],
            $result['heroes'],
            $result['skins']
        ));

        return self::SUCCESS;
    }
}
