<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking highlighted_skins in database ===\n\n";

$attrs = \App\Models\AccountAttribute::where('attribute_key', 'highlighted_skins')->get();

if ($attrs->isEmpty()) {
    echo "NO highlighted_skins found in database!\n";
} else {
    echo "Found " . $attrs->count() . " accounts with highlighted_skins:\n\n";
    foreach ($attrs as $attr) {
        echo "Account ID: " . $attr->account_id . "\n";
        echo "Value: " . $attr->attribute_value . "\n";
        echo "---\n\n";
    }
}

echo "\n=== Testing search pattern ===\n\n";

// Simulate what the filter receives - TEST WITH CORRECT HERO
$testFilter = "assassin-alucard-child-of-the-fall";
echo "Filter received: $testFilter\n";

$parts = explode('-', $testFilter);
echo "Parts: " . json_encode($parts) . "\n";

if (count($parts) >= 3) {
    $role = $parts[0];
    $hero = $parts[1];
    $skinName = implode(' ', array_slice($parts, 2));
    
    echo "Parsed - Role: $role, Hero: $hero, Skin: $skinName\n";
    
    $searchPattern = strtolower($hero) . ' - ' . strtolower($skinName);
    echo "Search pattern: \"$searchPattern\"\n\n";
    
    // Try to find matching accounts
    $matches = \App\Models\AccountForSale::whereHas('attributes', function($q) use ($searchPattern) {
        $q->where('attribute_key', 'highlighted_skins')
          ->whereRaw('LOWER(attribute_value) LIKE ?', ['%' . $searchPattern . '%']);
    })->with(['attributes' => function($q) {
        $q->where('attribute_key', 'highlighted_skins');
    }])->get();
    
    echo "Found " . $matches->count() . " matching accounts\n";
    foreach ($matches as $account) {
        $skinAttr = $account->attributes->where('attribute_key', 'highlighted_skins')->first();
        if ($skinAttr) {
            echo "  Account #" . $account->id . ": " . $skinAttr->attribute_value . "\n";
        }
    }
}
