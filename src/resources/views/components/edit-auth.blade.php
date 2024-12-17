@auth
@if (Auth::user()->hasRole('administrator'))
<x-pondol-meta::edit :meta="$meta" />
@endif
@endauth