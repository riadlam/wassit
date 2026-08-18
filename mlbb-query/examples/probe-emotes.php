<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== mapi icon map emote keys ===\n";
$icons = Illuminate\Support\Facades\Http::timeout(30)->get('https://mapi.mobilelegends.com/api/icon')->json();
if (is_array($icons)) {
    $emoteKeys = array_values(array_filter(array_keys($icons), fn ($k) => stripos($k, 'emote') !== false));
    echo 'Total icons: '.count($icons)."\n";
    echo 'Emote-like keys: '.count($emoteKeys)."\n";
    echo json_encode(array_slice($emoteKeys, 0, 10), JSON_PRETTY_PRINT)."\n\n";
}

$candidates = [
    'https://mapi.mobilelegends.com/emote/list',
    'https://mapi.mobilelegends.com/battle/emote/list',
    'https://mapi.mobilelegends.com/api/emote',
];

echo "=== mapi emote endpoint probes ===\n";
foreach ($candidates as $url) {
    $response = Illuminate\Support\Facades\Http::timeout(10)->get($url);
    echo $url.' => '.$response->status().' | '.substr($response->body(), 0, 80)."\n";
}

echo "\n=== Fandom Battle emotes page sections ===\n";
$sections = Illuminate\Support\Facades\Http::timeout(20)->get('https://mobile-legends.fandom.com/api.php', [
    'action' => 'parse',
    'page' => 'Battle emotes',
    'prop' => 'sections',
    'format' => 'json',
])->json('parse.sections', []);

echo 'Section count: '.count($sections)."\n";
foreach (array_slice($sections, 0, 8) as $section) {
    echo ($section['line'] ?? '').' (index '.($section['index'] ?? '').")\n";
}

echo "\n=== Miya hero page emote-related sections ===\n";
$miyaSections = Illuminate\Support\Facades\Http::timeout(20)->get('https://mobile-legends.fandom.com/api.php', [
    'action' => 'parse',
    'page' => 'Miya',
    'prop' => 'sections',
    'format' => 'json',
])->json('parse.sections', []);

foreach ($miyaSections as $section) {
    $line = (string) ($section['line'] ?? '');
    if (stripos($line, 'emote') !== false) {
        echo json_encode($section)."\n";
    }
}
