@php
 list($meta, $og, $twitter) = $meta->toArray();
@endphp

@foreach($meta as $k=> $v)
<meta name="{{$k}}" content="{{ $v }}"/>
@endforeach
<meta name="url" content="{{ request()->fullUrl() }}">
@foreach($og as $k=> $v)
<meta property="og:{{$k}}" content="{{ $v }}"/>
@endforeach
<meta property="og:url" content="{{ request()->fullUrl() }}"/>
@foreach($twitter as $k=> $v)
<meta name="twitter:{{$k}}" content="{{ $v }}"/>
@endforeach
