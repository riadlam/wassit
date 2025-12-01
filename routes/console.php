<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Support\SkinsHelper;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('skins:convert-legacy', function () {
    $this->info('Starting legacy highlighted_skins conversion to IDs...');
    $total = 0; $converted = 0; $skipped = 0;
    $rows = DB::table('account_attributes')
        ->select('id', 'attribute_value')
        ->where('attribute_key', 'highlighted_skins')
        ->get();
    foreach ($rows as $row) {
        $total++;
        $val = trim((string) $row->attribute_value);
        if ($val === '') { $skipped++; continue; }
        if (preg_match('/^\d+(,\d+)*$/', $val)) { $skipped++; continue; }
        $normalized = SkinsHelper::normalizeHighlightedSkins($val);
        if ($normalized && $normalized !== $val) {
            DB::table('account_attributes')->where('id', $row->id)->update(['attribute_value' => $normalized]);
            $converted++;
            $this->line("Converted ID={$row->id}: {$val} -> {$normalized}");
        } else { $skipped++; }
    }
    $this->info("Processed: {$total}, Converted: {$converted}, Skipped: {$skipped}");
})->purpose('Convert legacy highlighted_skins text to numeric IDs');
