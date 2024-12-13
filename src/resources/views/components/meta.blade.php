<meta name="title" content="{{ $meta->title }}"/>
<meta name="keywords" content="{{ $meta->keywords }}"/>
<meta name="description" content="{{ $meta->description }}"/>
<meta name="robots" content="{{config('pondol-meta.robots')}}"/> 
<meta name="revisit-after" content="{{config('pondol-meta.revisit-after')}}">
<meta name="coverage" content="{{config('pondol-meta.coverage')}}">
<meta name="distribution" content="{{config('pondol-meta.distribution')}}">
<meta name="url" content="{{ request()->fullUrl() }}">
<meta name="rating" content="{{config('pondol-meta.rating')}}">

@isset($meta->og->image)
<meta property="og:image" content="{{ config('app.url').$meta->og->image }}"/>
@endisset

<meta property="og:title" content="{{ $meta->title }}"/>
<meta property="og:description" content="{{ $meta->description }}"/>
<meta property="og:type" content="{{ $meta->og_type }}"/>
<meta property="og:locale" content="ko_kr" />
<meta property="og:site_name" content="{{ config('app.name', '온스토리') }}" />
<meta property="og:url" content="{{ request()->fullUrl() }}"/>
<meta name="twitter:card" content="summary"/>
<meta name="twitter:title" content="{{ $meta->title }}" /> 
<meta name="twitter:description" content="{{ $meta->description }}" /> 
@isset($meta->og->image)
<meta name="twitter:image" content="{{ config('app.url').$meta->og->image }}"/>
@endisset
{{--
<meta name="twitter:site" content="{{ setting('site.twitter_site') }}"/>
<meta name="twitter:creator" content="{{ setting('site.twitter_creator') }}"/>
--}}