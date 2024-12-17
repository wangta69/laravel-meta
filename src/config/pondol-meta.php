<?php
return [

  "robots"=>"index,follow",
  "revisit-after"=>"7 days",
  "coverage"=>"Worldwide",
  "distribution"=>"Global",
  "rating"=>"General",

  "dummy_image" => [
    "save_path" => public_path()."/images",
    "background_image"=> public_path()."/images/seo-default.jpg",
    "font" => public_path()."/fonts/NanumGothic.ttf",
    "fontSize" => 24
  ],
  'route_sitemap'=>[
    'prefix'=>'meta',
    'as'=>'meta.',
    'middleware'=>['web'],
  ],

  'route_meta_admin'=>[
    'prefix'=>'meta/admin',
    'as'=>'meta.admin.',
    'middleware'=>['web', 'admin'],
  ],
  'component' => ['admin'=>['layout'=>'pondol-meta::admin', 'lnb'=>'pondol-meta::partials.admin-lnb']],
];
