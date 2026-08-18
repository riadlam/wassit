<?php

require __DIR__.'/../vendor/autoload.php';

use MobaGuides\MobileLegendsApi\Fetchers\Hero;
use MobaGuides\MobileLegendsApi\MobileLegends;

$hero = MobileLegends::make(Hero::class);

echo "=== LIST (first hero) ===\n";
echo json_encode($hero->all()->first(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n\n";

echo "=== DETAIL (id=1) ===\n";
echo json_encode($hero->detail(1), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
