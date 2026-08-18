<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = app(App\Services\MlbbFandomService::class);

foreach (['Novaria', 'Fredrinn'] as $hero) {
    $data = $service->getHeroSkins($hero);
    echo "=== {$hero} ===\n";
    foreach ($data['skins'] as $skin) {
        $tags = implode(',', array_column($skin['tags'] ?? [], 'name'));
        echo sprintf("%-28s rarity=%-16s painted=%s tags=%s\n", $skin['name'], $skin['rarity'], !empty($skin['painted']) ? 'Y' : 'n', $tags);
    }
    echo "\n";
}
