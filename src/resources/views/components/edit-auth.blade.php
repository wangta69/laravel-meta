{{--
    src/resources/views/components/edit-auth.blade.php
--}}
@props(['meta', 'skin' => null])

@auth
    @if (Auth::user()->hasRole('administrator'))
        <x-pondol-meta::edit :meta="$meta" :skin="$skin" {{ $attributes }} />
    @endif
@endauth
