<?php

namespace App\Console\Commands;

use App\Services\MlbbCatalogSyncService;
use Illuminate\Console\Command;

class SyncMlbbCatalog extends Command
{
    protected $signature = 'mlbb:sync-catalog {type=all : emotes, recalls, or all}';

    protected $description = 'Fetch MLBB emotes and/or recalls from the Fandom wiki and store them in the database';

    public function handle(MlbbCatalogSyncService $catalog): int
    {
        $type = strtolower((string) $this->argument('type'));

        if (! in_array($type, ['all', 'emotes', 'recalls'], true)) {
            $this->error('Type must be emotes, recalls, or all.');

            return self::FAILURE;
        }

        set_time_limit(180);

        if ($type === 'all' || $type === 'emotes') {
            $this->info('Checking emotes against the Fandom wiki...');
            $this->report($catalog->checkEmotes(true), 'emotes');
        }

        if ($type === 'all' || $type === 'recalls') {
            $this->info('Checking recalls against the Fandom wiki...');
            $this->report($catalog->checkRecalls(true), 'recalls');
        }

        return self::SUCCESS;
    }

    /**
     * @param  array{remote_count: int, db_count: int, added_count: int, added: array<int, string>}  $result
     */
    private function report(array $result, string $label): void
    {
        $this->info(sprintf(
            'Wiki %s: %d | DB: %d | New saved: %d',
            $label,
            $result['remote_count'],
            $result['db_count'],
            $result['added_count']
        ));

        foreach ($result['added'] as $name) {
            $this->line('  + '.$name);
        }
    }
}
