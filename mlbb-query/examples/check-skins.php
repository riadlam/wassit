<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$response = Illuminate\Support\Facades\Http::get('https://mapi.mobilelegends.com/hero/detail?id=1')->object();
$data = (array) ($response->data ?? new stdClass);

echo "Top-level keys:\n";
echo json_encode(array_keys($data), JSON_PRETTY_PRINT)."\n\n";

foreach (['skin', 'skins', 'skin_list', 'hero_skin'] as $key) {
    if (isset($data[$key])) {
        echo "Found key '{$key}':\n";
        echo json_encode($data[$key], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n\n";
    }
}

// Search nested keys containing 'skin'
$json = json_encode($data);
preg_match_all('/"([^"]*skin[^"]*)"/i', $json, $matches);
echo "Keys/paths containing 'skin':\n";
echo json_encode(array_values(array_unique($matches[1] ?? [])), JSON_PRETTY_PRINT)."\n\n";

$candidates = [
    'https://mapi.mobilelegends.com/hero/skin?id=1',
    'https://mapi.mobilelegends.com/skin/list',
    'https://mapi.mobilelegends.com/hero/skinlist?id=1',
];

echo "Probing possible skin endpoints:\n";
foreach ($candidates as $url) {
    $response = Illuminate\Support\Facades\Http::timeout(10)->get($url);
    echo $url.' => HTTP '.$response->status().' | '.substr($response->body(), 0, 120)."\n";
}
