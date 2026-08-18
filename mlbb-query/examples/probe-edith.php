<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function wikiGet(array $query): array
{
    return Illuminate\Support\Facades\Http::timeout(25)
        ->withHeaders(['User-Agent' => 'WasitMlbbProbe/1.0'])
        ->get('https://mobile-legends.fandom.com/api.php', $query)
        ->json() ?? [];
}

foreach (['Common skins', 'Exquisite skins', 'Exceptional skins', 'Deluxe skins', 'Collector Skins', 'StarLight'] as $page) {
    $wt = wikiGet(['action' => 'parse', 'page' => $page, 'prop' => 'wikitext', 'format' => 'json'])['parse']['wikitext']['*'] ?? '';
    echo "{$page}: ".(stripos($wt, 'Verdant Knight') !== false ? 'YES' : 'no')."\n";
    if (stripos($wt, 'Verdant Knight') !== false && preg_match('/.{0,80}Verdant Knight.{0,80}/s', $wt, $m)) {
        echo "  {$m[0]}\n";
    }
}

echo "\n=== search ===\n";
$search = wikiGet(['action' => 'query', 'list' => 'search', 'srsearch' => '"Verdant Knight"', 'srlimit' => 8, 'format' => 'json']);
foreach ($search['query']['search'] ?? [] as $hit) {
    echo "- {$hit['title']}\n{$hit['snippet']}\n\n";
}

$splash = wikiGet(['action' => 'parse', 'page' => 'Edith', 'section' => 9, 'prop' => 'wikitext', 'format' => 'json'])['parse']['wikitext']['*'] ?? '';
echo "=== splash 9 ===\n{$splash}\n";
