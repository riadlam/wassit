<?php

return [
    'width' => 681,
    'height' => 1024,
    'export_width' => 1080,

    /** Emails that receive the premium poster layout (featured skins + 6-tile gallery). */
    'premium_emails' => [
        'riadlaamari@gmail.com',
    ],

    /** Optional override, e.g. C:\Program Files\Google\Chrome\Application\chrome.exe */
    'chrome_path' => env('BROWSERSHOT_CHROME_PATH'),

    'price' => [
        'premium' => [
            'left' => 178,
            'top' => 868,
            'width' => 414,
            'height' => 44,
            'font_size' => 44,
            'rotate' => -10,
            'translate_x' => 4,
            'translate_y' => 0,
        ],
        'basic' => [
            'left' => 173,
            'top' => 922,
            'width' => 296,
            'height' => 48,
            'font_size' => 36,
            'rotate' => -10,
            'translate_x' => 2,
            'translate_y' => 0,
        ],
    ],
];
