{{-- src/resources/views/components/meta.blade.php --}}
@php
    [$meta, $og, $twitter] = $meta->toArray();
@endphp

{{-- Canonical 태그 자동 출력 로직 --}}
@if (isset($__data['meta']->canonical) && $__data['meta']->canonical)
    <link rel="canonical" href="{{ $__data['meta']->canonical }}" />
@endif
{{-- Canonical 태그 자동 출력 로직 --}}

{{-- SEO Meta Tags --}}
@foreach ($meta as $k => $v)
    <meta name="{{ $k }}" content="{{ $v }}" />
@endforeach

{{-- Open Graph Meta Tags --}}
@foreach ($og as $k => $v)
    <meta property="og:{{ $k }}" content="{{ $v }}" />
@endforeach
<meta property="og:url" content="{{ request()->fullUrl() }}" />

{{-- Twitter Card Meta Tags --}}
@foreach ($twitter as $k => $v)
    <meta name="twitter:{{ $k }}" content="{{ $v }}" />
@endforeach

{{--  JSON-LD 스크립트 자동 출력 로직  --}}
@if (isset($__data['meta']->structuredData) && !empty($__data['meta']->structuredData))
    <script type="application/ld+json">
    @json($__data['meta']->structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
</script>
@endif
{{-- JSON-LD 스크립트 자동 출력 로직 --}}
