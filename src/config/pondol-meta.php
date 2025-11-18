<?php

return [
  'defaults' => ['image' => '/pondol/meta/default.png'],
  'structured_data' => [
        'author' => [
            '@type' => 'Organization',
            'name' => '온스토리',
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => '온스토리',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => '/logo.png',
            ],
        ],
    ],
  "robots" => "index,follow",
  "revisit-after" => "7 days",
  "coverage" => "Worldwide",
  "distribution" => "Global",
  "rating" => "General",

  "dummy_image" => [
    "save_path" => public_path()."/pondol/meta",
    "background_image" => public_path()."/pondol/meta/seo-default.jpg",
    "font" => public_path()."/pondol/meta/NanumGothic.ttf",
    "fontSize" => 24
  ],
  'route_sitemap' => [
    'prefix' => 'meta',
    'as' => 'meta.',
    'middleware' => [],
  ],

  'route_meta_admin' => [
    'prefix' => 'meta/admin',
    'as' => 'meta.admin.',
    'middleware' => ['web', 'admin'],
  ],
  'component' => ['admin' => ['layout' => 'pondol-common::common-admin', 'lnb' => 'pondol-meta::partials.admin-lnb']],
];
