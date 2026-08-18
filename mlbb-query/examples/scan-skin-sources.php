<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$urls = [
    'https://mlbb.rone.dev/api/mlbb/heroes/1?lang=en',
    'https://mlbb.rone.dev/api/heroes/1?lang=en',
    'https://mlbb.rone.dev/api/academy/heroes/1?lang=en',
    'https://mlbb.rone.dev/api/academy/heroes?size=1&lang=en',
];

foreach ($urls as $url) {
    echo "=== {$url} ===\n";
    try {
        $response = Illuminate\Support\Facades\Http::timeout(20)->get($url);
        echo 'Status: '.$response->status()."\n";
        $json = json_encode($response->json());
        echo 'Has skin word: '.(stripos($json, 'skin') !== false ? 'YES' : 'NO')."\n";
        echo substr($json, 0, 400)."\n\n";
    } catch (Throwable $exception) {
        echo 'Error: '.$exception->getMessage()."\n\n";
    }
}

echo "=== Icon map skin keys sample ===\n";
$iconResponse = Illuminate\Support\Facades\Http::timeout(30)->get('https://mapi.mobilelegends.com/api/icon');
$icons = $iconResponse->json();
if (is_array($icons)) {
    $skinKeys = array_values(array_filter(array_keys($icons), fn ($k) => stripos($k, 'skin') !== false));
    echo 'Total icons: '.count($icons)."\n";
    echo 'Skin-like keys: '.count($skinKeys)."\n";
    echo json_encode(array_slice($skinKeys, 0, 15), JSON_PRETTY_PRINT)."\n";
    if ($skinKeys !== []) {
        $sample = $skinKeys[0];
        echo "Sample {$sample} => {$icons[$sample]}\n";
    }
}
