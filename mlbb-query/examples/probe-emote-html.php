<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fetch = function (string $page, int $section) {
    $text = Illuminate\Support\Facades\Http::timeout(25)->get('https://mobile-legends.fandom.com/api.php', [
        'action' => 'parse',
        'page' => $page,
        'section' => $section,
        'prop' => 'text',
        'format' => 'json',
    ])->json('parse.text');

    return is_array($text) ? (string) ($text['*'] ?? '') : (string) $text;
};

echo "=== Miya battle emotes section 13 (first 2000 chars) ===\n";
echo substr($fetch('Miya', 13), 0, 2000)."\n\n";

echo "=== Battle emotes 2025 section 3 (first 2000 chars) ===\n";
echo substr($fetch('Battle emotes', 3), 0, 2000)."\n";
