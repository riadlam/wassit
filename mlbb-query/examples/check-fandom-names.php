<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$names = ['Popol and Kupa', 'Popol & Kupa', 'Luo Yi', 'Yi Sun-shin', 'X.Borg', 'Selena', 'Selina', 'Miya'];

foreach ($names as $name) {
    $response = Illuminate\Support\Facades\Http::timeout(15)->get('https://mobile-legends.fandom.com/api.php', [
        'action' => 'parse',
        'page' => $name,
        'prop' => 'sections',
        'format' => 'json',
    ]);

    $title = $response->json('parse.title');
    $error = $response->json('error.code');
    echo ($title ?: $error ?: 'fail').' <= '.$name.PHP_EOL;
}
