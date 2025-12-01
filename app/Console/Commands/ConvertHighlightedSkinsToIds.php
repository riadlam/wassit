<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Support\SkinsHelper;

class ConvertHighlightedSkinsToIds extends Command
{
    protected $signature = 'skins:convert-legacy';
    protected $description = 'Convert legacy highlighted_skins values (hero - skin text) to comma-separated numeric IDs';

    public function handle(): int
    {
        $this->info('Starting legacy highlighted_skins conversion to IDs...');

        $total = 0;
        $converted = 0;
        $skipped = 0;

        $rows = DB::table('account_attributes')
            ->select('id', 'attribute_value')
            ->where('attribute_key', 'highlighted_skins')
            ->get();

        foreach ($rows as $row) {
            $total++;
            $val = trim((string) $row->attribute_value);
            if ($val === '') { $skipped++; continue; }

            // If already IDs (digits and commas), skip
            if (preg_match('/^\d+(,\d+)*$/', $val)) { $skipped++; continue; }

            $normalized = SkinsHelper::normalizeHighlightedSkins($val);
            if ($normalized && $normalized !== $val) {
                DB::table('account_attributes')->where('id', $row->id)->update(['attribute_value' => $normalized]);
                $converted++;
                $this->line("Converted ID={$row->id}: {$val} -> {$normalized}");
            } else {
                $skipped++;
            }
        }

        $this->info("Processed: {$total}, Converted: {$converted}, Skipped: {$skipped}");
        $this->info('Done.');
        return Command::SUCCESS;
    }
}
