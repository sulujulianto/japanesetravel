<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Brand identity
    |--------------------------------------------------------------------------
    |
    | Keep identity separate from page copy. A rebrand should not require
    | editing Vue components, Blade layouts, payment drivers, or admin pages.
    | APP_NAME remains the canonical product name used by Laravel itself.
    |
    */
    'name' => env('APP_NAME', 'Japan Travel'),
    'mark' => env('BRAND_MARK', 'JT'),
    'legal_name' => env('BRAND_LEGAL_NAME', env('APP_NAME', 'Japan Travel')),

    'region' => [
        'id' => env('BRAND_REGION_ID', 'Jepang'),
        'en' => env('BRAND_REGION_EN', 'Japan'),
    ],
];
