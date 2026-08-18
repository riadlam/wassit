<?php

require __DIR__.'/../../vendor/autoload.php';

$html = file_get_contents(__DIR__.'/hayabusa-cosmetics.html');
libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML('<?xml encoding="utf-8" ?>'.$html);
$xpath = new DOMXPath($doc);

$boxes = $xpath->query('//div[contains(@class,"skin-box") and not(contains(@class,"skin-box-"))]');
echo "skin-box count: ".$boxes->length."\n\n";

foreach ($boxes as $i => $box) {
    if ($i > 12) break;
    if (! $box instanceof DOMElement) continue;
    $name = trim($xpath->query('.//*[contains(@class,"skin-box-name")]', $box)->item(0)?->textContent ?? '');
    $borderImg = $xpath->query('.//*[contains(@class,"skin-box-border")]//img', $box)->item(0);
    $border = $borderImg instanceof DOMElement
        ? ($borderImg->getAttribute('data-image-name') ?: $borderImg->getAttribute('alt'))
        : '';
    $tags = [];
    foreach ($xpath->query('.//*[contains(@class,"skin-box-tag")]//img', $box) as $img) {
        $tags[] = $img->getAttribute('data-image-name') ?: $img->getAttribute('alt');
    }
    echo ($i+1).". {$name}\n   border: {$border}\n   tags: ".implode(', ', $tags)."\n";
}
