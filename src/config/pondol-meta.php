<?php
return [

  "robots"=>"index,follow",
  "revisit-after"=>"7 days",
  "coverage"=>"Worldwide",
  "distribution"=>"Global",
  "rating"=>"General",

  'route_meta_admin'=>[
    'prefix'=>'meta/admin',
    'as'=>'meta.admin.',
    'middleware'=>['web', 'admin'],
  ],
  'component' => ['admin'=>['layout'=>'pondol-meta::admin', 'lnb'=>'pondol-meta::partials.admin-lnb']],
];
