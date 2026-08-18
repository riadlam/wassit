<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$html = Illuminate\Support\Facades\Http::timeout(25)
    ->withHeaders(['User-Agent' => 'WasitMlbbProbe/1.0'])
    ->get('https://mobile-legends.fandom.com/api.php', [
        'action' => 'parse',
        'page' => 'Miya/Cosmetics',
        'prop' => 'text',
        'format' => 'json',
    ])->json()['parse']['text']['*'] ?? '';

libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML('<?xml encoding="utf-8" ?>'.$html);
$xpath = new DOMXPath($doc);

foreach ($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " skin-box ")]') as $box) {
    $name = trim($xpath->query('.//*[contains(@class,"skin-box-name")]', $box)->item(0)?->textContent ?? '');
    $heading = '';
    $current = $box;
    while ($current) {
        $sib = $current->previousSibling;
        while ($sib) {
            if ($sib instanceof DOMElement && in_array(strtolower($sib->nodeName), ['h1','h2','h3'], true)) {
                $heading = trim($sib->textContent);
                break 2;
            }
            $sib = $sib->previousSibling;
        }
        $current = $current->parentNode instanceof DOMElement ? $current->parentNode : null;
    }
    $tags = [];
    foreach ($xpath->query('.//*[contains(@class,"skin-box-tag")]//img', $box) as $img) {
        $tags[] = $img->getAttribute('data-image-name');
    }
    $borderImg = $xpath->query('.//*[contains(@class,"skin-box-border")]//img', $box)->item(0);
    $border = $borderImg?->getAttribute('data-image-name');
    echo "[{$heading}] {$name} | {$border} | ".implode(',', $tags)."\n";
}
