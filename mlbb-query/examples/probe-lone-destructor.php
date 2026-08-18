<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$heroes = app(App\Services\MlbbApiService::class)->listHeroes();
$hero = collect($heroes)->first(fn ($item) => (int) $item['id'] === 30);
echo "=== hero 30 ===\n";
echo json_encode($hero, JSON_PRETTY_PRINT)."\n\n";

$heroName = $hero['name'] ?? '';
$service = app(App\Services\MlbbFandomService::class);
$data = $service->getHeroSkins($heroName);

echo "=== {$heroName} skins ===\n";
foreach ($data['skins'] as $skin) {
    $tags = implode(',', array_column($skin['tags'] ?? [], 'name'));
    $mark = stripos($skin['name'], 'destructor') !== false ? ' <<<<' : '';
    echo sprintf(
        "%-32s rarity=%-16s border=%-12s painted=%s tags=%s%s\n",
        $skin['name'],
        $skin['rarity'] ?? '',
        $skin['border'] ?? '',
        ! empty($skin['painted']) ? 'Y' : 'n',
        $tags,
        $mark
    );
}

function wikiGet(array $query): array
{
    return Illuminate\Support\Facades\Http::timeout(25)
        ->withHeaders(['User-Agent' => 'WasitMlbbProbe/1.0'])
        ->get('https://mobile-legends.fandom.com/api.php', $query)
        ->json() ?? [];
}

echo "\n=== catalog pages ===\n";
foreach (['Collector Skins', 'Exquisite skins', 'Exceptional skins', 'Deluxe skins', 'StarLight', 'Common skins'] as $page) {
    $wt = wikiGet(['action' => 'parse', 'page' => $page, 'prop' => 'wikitext', 'format' => 'json'])['parse']['wikitext']['*'] ?? '';
    $hit = stripos($wt, 'Lone Destructor') !== false;
    echo "{$page}: ".($hit ? 'YES' : 'no')."\n";
    if ($hit && preg_match('/.{0,90}Lone Destructor.{0,90}/s', $wt, $m)) {
        echo "  {$m[0]}\n";
    }
}

$module = wikiGet(['action' => 'parse', 'page' => 'Module:Skin/data', 'prop' => 'wikitext', 'format' => 'json'])['parse']['wikitext']['*'] ?? '';
if (preg_match('/.{0,80}\["'.preg_quote($heroName, '/').'"\].{0,40}/s', $module, $m)) {
    echo "\n=== module hero header ===\n{$m[0]}\n";
}
if (preg_match('/Lone Destructor.{0,250}/s', $module, $m)) {
    echo "\n=== module Lone Destructor ===\n{$m[0]}\n";
}
