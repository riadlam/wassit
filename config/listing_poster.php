<?php

return [
    'width' => 681,
    'height' => 1024,
    'export_width' => 1080,

    /** Emails that receive the premium poster layout (featured skins + 6-tile gallery). */
    'premium_emails' => [
        'riadlaamari@gmail.com',
    ],

    'price' => [
        'premium' => [
            'left' => 168,
            'top' => 870,
            'width' => 218,
            'height' => 46,
            'font_size' => 42,
            'rotate' => -10,
            'translate_x' => 0,
            'translate_y' => 0,
            /** Extra upward nudge for html2canvas export (preview uses translate_y only). */
            'export_translate_y' => -22,
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
