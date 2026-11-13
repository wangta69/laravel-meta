{{-- src/resources/views/components/edit.blade.php --}}
@props(['meta', 'skin' => null])

@php
    $selectedSkin = $skin ?: (config('pondol-meta.skin') ?: 'default');
    $skinView = "pondol-meta::skins.{$selectedSkin}.edit";

    if (!view()->exists($skinView)) {
        // 스킨명 대신 전체 뷰 경로(예: 'partials.custom-meta-edit')가 지정된 경우 지원
        // 둘 다 없으면 안전하게 default 스킨으로 폴백
        $skinView = view()->exists($selectedSkin) ? $selectedSkin : 'pondol-meta::skins.default.edit';
    }
@endphp

@include($skinView, [
    'meta' => $meta,
    'attributes' => $attributes,
])
