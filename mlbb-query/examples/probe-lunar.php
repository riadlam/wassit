<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function wikiGet(array $query): array
{
    return Illuminate\Support\Facades\Http::timeout(30)
        ->withHeaders(['User-Agent' => 'WasitMlbbProbe/1.0'])
        ->get('https://mobile-legends.fandom.com/api.php', $query)
        ->json() ?? [];
}

foreach (['Exceptional skins', 'Deluxe skins', 'Exquisite skins'] as $page) {
    $html = wikiGet(['action' => 'parse', 'page' => $page, 'prop' => 'text', 'format' => 'json'])['parse']['text']['*'] ?? '';
    echo "{$page} 1195=".(stripos($html, '1195')!==false?'yes':'no')." Lunar=".(stripos($html, 'Lunar')!==false?'yes':'no')." Scion=".(stripos($html, 'Scion')!==false?'yes':'no')."\n";
}

$data = wikiGet(['action' => 'query', 'titles' => 'File:Hero1195-icon.png|File:Hero1195-portrait.png', 'prop' => 'imageinfo|categories', 'iiprop' => 'url|extmetadata', 'format' => 'json']);
echo json_encode($data['query']['pages'] ?? [], JSON_PRETTY_PRINT);
