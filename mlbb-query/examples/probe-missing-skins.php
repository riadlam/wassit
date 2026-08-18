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

foreach (['Upcoming skins', 'Upcoming', 'Patch Notes 2.2', 'Novaria'] as $page) {
    $data = wikiGet(['action' => 'parse', 'page' => $page, 'prop' => 'wikitext', 'format' => 'json']);
    if (isset($data['error'])) {
        echo "{$page}: {$data['error']['code']}\n";
        continue;
    }
    $wt = $data['parse']['wikitext']['*'] ?? '';
    echo "=== {$page} Lunar=". (stripos($wt, 'Lunar Scion') !== false ? 'yes' : 'no')." Dragonsworn=".(stripos($wt, 'Dragonsworn') !== false ? 'yes' : 'no')." ===\n";
}

$search = wikiGet(['action' => 'query', 'list' => 'search', 'srsearch' => 'Novaria Dragonsworn Collector', 'srlimit' => 5, 'format' => 'json']);
foreach ($search['query']['search'] ?? [] as $hit) {
    echo "- {$hit['title']}\n";
}

// File png
$data = wikiGet(['action' => 'query', 'titles' => 'File:Novaria (Lunar Scion).png', 'prop' => 'imageinfo|categories', 'iiprop' => 'url|extmetadata', 'format' => 'json']);
echo json_encode($data['query']['pages'] ?? [], JSON_PRETTY_PRINT);
