@php
 list($meta, $og, $twitter) = $meta->toArray();
@endphpcd 

  <!-- SEO Meta Tags -->
@foreach($meta as $k=> $v)
  <meta name="{{$k}}" content="{{ $v }}"/>
@endforeach

  <!-- Open Graph Meta Tags -->
@foreach($og as $k=> $v)
  <meta property="og:{{$k}}" content="{{ $v }}"/>
@endforeach
  <meta property="og:url" content="{{ request()->fullUrl() }}"/>

  <!-- Twitter Card Meta Tags -->
@foreach($twitter as $k=> $v)
  <meta name="twitter:{{$k}}" content="{{ $v }}"/>
@endforeach
