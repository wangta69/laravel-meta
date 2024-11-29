<meta name="title" content="{{ $meta->title }}"/>
<meta name="keywords" content="{{ $meta->keywords }}"/>
<meta name="description" content="{{ $meta->description }}"/>
<meta name="robots" content="index,follow"/> 
<meta name="revisit-after" content="7 days">
<meta name="coverage" content="Worldwide">
<meta name="distribution" content="Global">
{{--
<meta name="author" content="{{ setting('site.title')}}">
--}}
<meta name="url" content="{{ request()->fullUrl() }}">
@isset($meta->og_image)
<meta property="og:image" content="{{ config('app.url').$meta->og_image->name }}"/>
{{-- 
<meta name="og_image" content="{{ config('app.url').$meta->og_image->name }}"/>
<meta name="og_secureImage" content="{{ config('app.url').$meta->og_image->name }}"/>
<meta name="og_imageAlt" content="{{ $meta->og_image->alt }}"/>
<meta name="og_imageType" content="{{ $meta->og_image->type }}"/>
--}}
@endisset
<meta name="rating" content="General">
<meta property="og:title" content="{{ $meta->title }}"/>
<meta property="og:description" content="{{ $meta->description }}"/>
<meta property="og:type" content="{{ $meta->og_type }}"/>
<meta property="og:locale" content="ko_kr" />
<meta property="og:site_name" content="{{ config('app.name', '온스토리') }}" />
<meta property="og:url" content="{{ request()->fullUrl() }}"/>
<meta name="twitter:card" content="summary"/>
<meta name="twitter:title" content="{{ $meta->title }}" /> 
<meta name="twitter:description" content="{{ $meta->description }}" /> 
@isset($meta->og_image)
<meta name="twitter:image" content="{{ config('app.url').$meta->og_image->name }}" />
@endisset
{{--
<meta name="twitter:site" content="{{ setting('site.twitter_site') }}"/>
<meta name="twitter:creator" content="{{ setting('site.twitter_creator') }}"/>
--}}