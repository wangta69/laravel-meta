@auth
    @if (Auth::user()->hasRole('administrator'))
        <div class="container mt-5">
            <form method="POST" action="{{ route('meta.admin.index-now', [$meta->id]) }}">
                @csrf
                <button type="submit" class="btn btn-success">Index-now</button>
            </form>
        </div>
    @endif
@endauth
