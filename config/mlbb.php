<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Heroes missing from mapi.mobilelegends.com/hero/list
    |--------------------------------------------------------------------------
    |
    | The official list endpoint is stale and omits newer heroes (e.g. Suyou).
    | These entries are merged into the create-listing hero picker and into
    | mlbb:sync-skins so skins can still be downloaded from the Fandom wiki.
    |
    */
    'supplemental_heroes' => [
        [
            'id' => 1261,
            'name' => 'Suyou',
            'role' => 'Assassin',
            'avatar_url' => 'https://static.wikia.nocookie.net/mobile-legends/images/4/41/Hero1261-portrait.png',
        ],
        [
            'id' => 1271,
            'name' => 'Lukas',
            'role' => 'Fighter',
            'avatar_url' => 'https://static.wikia.nocookie.net/mobile-legends/images/b/b0/Hero1271-portrait.png',
        ],
        [
            'id' => 1281,
            'name' => 'Kalea',
            'role' => 'Support',
            'avatar_url' => 'https://static.wikia.nocookie.net/mobile-legends/images/9/9a/Hero1281-portrait.png',
        ],
        [
            'id' => 1251,
            'name' => 'Zhuxin',
            'role' => 'Mage',
            'avatar_url' => 'https://static.wikia.nocookie.net/mobile-legends/images/9/9f/Hero1251-portrait.png',
        ],
    ],
];
