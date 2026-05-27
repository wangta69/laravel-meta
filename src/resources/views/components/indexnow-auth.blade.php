{{-- resources/views/components/indexnow-auth.blade.php --}}
@auth
    {{-- 1. 관리자인지 확인 --}}
    @if (Auth::user()->hasRole('administrator'))
        {{-- 2. $meta 객체가 존재하고 id값이 있는지 안전하게 체크 --}}
        @if (isset($meta) && isset($meta->id))
            <div class="container mt-5">
                <form method="POST" action="{{ route('meta.admin.index-now', [$meta->id]) }}">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-search me-1"></i> Index-now (ID: {{ $meta->id }})
                    </button>
                </form>
            </div>
        @else
            {{-- 메타 정보가 DB에 등록되지 않은 페이지일 경우 관리자에게 안내 --}}
            <div class="container mt-2">
                <small class="text-danger">* 이 페이지는 아직 메타 정보가 DB에 저장되지 않아 Index-now를 실행할 수 없습니다.</small>
            </div>
        @endif
    @endif
@endauth
