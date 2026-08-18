<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$html = Illuminate\Support\Facades\Http::timeout(25)
    ->withHeaders(['User-Agent' => 'WasitMlbbProbe/1.0'])
    ->get('https://mobile-legends.fandom.com/api.php', [
        'action' => 'parse',
        'page' => 'Battle effects',
        'prop' => 'text',
        'format' => 'json',
    ])->json()['parse']['text']['*'] ?? '';

$start = stripos($html, 'Recall Effects');
$end = stripos($html, 'Elimination Effects', $start ?: 0);
$chunk = $start !== false ? substr($html, $start, ($end ?: strlen($html)) - $start) : '';

libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML('<?xml encoding="utf-8" ?>'.$chunk);
$xpath = new DOMXPath($doc);

$items = [];
$tier = 'Common';

foreach ($xpath->query('//body/*') as $node) {
    if ($node instanceof DOMElement && in_array($node->nodeName, ['h3', 'h4'], true)) {
        $tier = trim(rtrim($node->textContent ?? '', "[] \t\n\r"));
        continue;
    }

    if (! $node instanceof DOMElement || $node->nodeName !== 'table') {
        continue;
    }

    foreach ($xpath->query('.//tr', $node) as $row) {
        $cells = $xpath->query('./td', $row);
        if ($cells->length < 2) continue;

        $name = trim(html_entity_decode(strip_tags($cells->item(0)->textContent ?? '')));
        if ($name === '') continue;

        $img = $xpath->query('.//img', $cells->item(1))->item(0);
        $desc = trim(html_entity_decode(strip_tags($cells->item(1)->textContent ?? '')));

        $thumb = null;
        if ($img instanceof DOMElement) {
            $thumb = $img->getAttribute('data-src') ?: $img->getAttribute('src');
            if ($thumb && str_starts_with($thumb, '//')) $thumb = 'https:'.$thumb;
        }

        $items[] = ['tier' => $tier, 'name' => $name, 'description' => $desc, 'thumbnail_url' => $thumb];
    }
}

echo json_encode([
    'source' => 'mobile-legends.fandom.com/wiki/Battle_effects#Recall_Effects',
    'count' => count($items),
    'sample' => array_slice($items, 0, 8),
    'fire_ice_themed' => array_values(array_filter($items, fn ($i) => preg_match('/fire|ice|snow|frost|flower|crown|flame|winter/i', $i['name'].' '.$i['description']))),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
