<header class="topbar d-flex align-items-center justify-content-between px-3">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" data-sidebar-toggle type="button">
            <i class="bi bi-list fs-5"></i>
        </button>
        <h1 class="h5 mb-0 d-none d-md-block">@yield('page-title', __('app.dashboard'))</h1>
    </div>

    <div class="d-flex align-items-center gap-2">
        {{-- Language switch --}}
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-translate"></i>
                {{ app()->getLocale() === 'bn' ? 'বাংলা' : 'English' }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item {{ app()->getLocale() === 'bn' ? 'active' : '' }}" href="{{ route('language.switch', 'bn') }}">বাংলা</a>
                </li>
                <li>
                    <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}" href="{{ route('language.switch', 'en') }}">English</a>
                </li>
            </ul>
        </div>

        {{-- Dark mode toggle --}}
        <button class="btn btn-sm btn-outline-secondary" type="button" data-theme-toggle title="{{ __('app.toggle_theme') }}">
            <i class="bi bi-moon-stars-fill" data-theme-icon></i>
        </button>

        {{-- User menu --}}
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <span class="badge rounded-circle bg-primary" style="width:1.6rem;height:1.6rem;line-height:1.2rem;">{{ auth()->user()->initials() }}</span>
                <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">{{ auth()->user()->role?->displayName() ?? __('app.no_role') }}</h6></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-1"></i> {{ __('app.logout') }}
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
