    <li>
      <a href="#meta-sub-menu" data-bs-toggle="collapse" 
        aria-expanded="{{ request()->routeIs(['auth.admin.users*']) ? 'true' : 'false' }}"
        class="dropdown-toggle">
          <i class="fa-solid fa-user"></i>
          메타관리
      </a>
      <ul class="collapse list-unstyled {{ request()->routeIs(['meta.admin.index']) ? 'show' : '' }}" id="meta-sub-menu">
        <li class="{{ request()->routeIs(['meta.admin.index']) ? 'current-page' : '' }}">
          <a href="{{ route('meta.admin.index') }}">메타관리</a>
        </li>
      </ul>
    </li>
