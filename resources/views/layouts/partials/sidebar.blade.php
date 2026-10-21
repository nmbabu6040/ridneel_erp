<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="brand-badge">R</span>
        <div>
            <div>{{ __('app.app_name') }}</div>
            <div class="small text-secondary fw-normal" style="font-size:.7rem">Ridneel Trade International</div>
        </div>
    </div>

    <nav class="nav flex-column py-2">
        <div class="nav-section-title">{{ __('app.nav_main') }}</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> {{ __('app.dashboard') }}
        </a>

        @if (auth()->user()->hasPermission('inventory.view') || auth()->user()->hasPermission('partners.view') || auth()->user()->hasPermission('stock.view') || auth()->user()->hasPermission('sales.view'))
            <div class="nav-section-title">{{ __('app.nav_operations') }}</div>
            @can('sales.view')
                <a href="{{ route('sales.index', ['type' => 'primary']) }}" class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                    <i class="bi bi-cart-check"></i> {{ __('app.sales') }}
                </a>
            @endcan
            @can('inventory.view')
                <a href="{{ route('inventory.products.index') }}" class="nav-link {{ request()->routeIs('inventory.products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> {{ __('app.products') }}
                </a>
                <a href="{{ route('inventory.categories.index') }}" class="nav-link {{ request()->routeIs('inventory.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i> {{ __('app.categories') }}
                </a>
                <a href="{{ route('inventory.units.index') }}" class="nav-link {{ request()->routeIs('inventory.units.*') ? 'active' : '' }}">
                    <i class="bi bi-rulers"></i> {{ __('app.units') }}
                </a>
                <a href="{{ route('inventory.warehouses.index') }}" class="nav-link {{ request()->routeIs('inventory.warehouses.*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i> {{ __('app.warehouses') }}
                </a>
            @endcan
            @can('stock.view')
                <a href="{{ route('inventory.stock.index') }}" class="nav-link {{ request()->routeIs('inventory.stock.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right"></i> {{ __('app.stock_ledger') }}
                </a>
            @endcan
            @can('partners.view')
                <a href="{{ route('partners.index', ['type' => 'dealer']) }}" class="nav-link {{ request()->routeIs('partners.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> {{ __('app.partners') }}
                </a>
            @endcan
        @endif

        @can('sr.attendance')
            <a href="{{ route('sr.attendance.index') }}" class="nav-link {{ request()->routeIs('sr.attendance.index') || request()->routeIs('sr.face.*') ? 'active' : '' }}">
                <i class="bi bi-camera-fill"></i> {{ __('app.attendance') }}
            </a>
        @endcan
        @can('sr.tracking.view')
            <a href="{{ route('sr.tracking.map') }}" class="nav-link {{ request()->routeIs('sr.tracking.map') ? 'active' : '' }}">
                <i class="bi bi-geo-alt-fill"></i> {{ __('app.live_tracking') }}
            </a>
            <a href="{{ route('sr.attendance.report') }}" class="nav-link {{ request()->routeIs('sr.attendance.report') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> {{ __('app.attendance_report') }}
            </a>
        @endcan

        @can('accounting.view')
            <div class="nav-section-title">{{ __('app.nav_accounting') }}</div>
            <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                <i class="bi bi-bag-plus"></i> {{ __('app.purchases') }}
            </a>
            <a href="{{ route('accounting.expenses.index') }}" class="nav-link {{ request()->routeIs('accounting.expenses.*') ? 'active' : '' }}">
                <i class="bi bi-receipt-cutoff"></i> {{ __('app.expenses') }}
            </a>
            <a href="{{ route('accounting.ledger.index') }}" class="nav-link {{ request()->routeIs('accounting.ledger.*') || request()->routeIs('accounting.vouchers.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i> {{ __('app.partner_ledger') }}
            </a>
            <a href="{{ route('accounting.reports.profit-loss') }}" class="nav-link {{ request()->routeIs('accounting.reports.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i> {{ __('app.profit_loss') }}
            </a>
            <a href="{{ route('accounting.account-heads.index') }}" class="nav-link {{ request()->routeIs('accounting.account-heads.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i> {{ __('app.chart_of_accounts') }}
            </a>
        @endcan

        @can('returns.view')
            <a href="{{ route('returns.index', ['type' => 'sales_return']) }}" class="nav-link {{ request()->routeIs('returns.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-repeat"></i> {{ __('app.returns_exchange') }}
            </a>
        @endcan
        @can('kpi.view')
            <a href="{{ route('kpi.report') }}" class="nav-link {{ request()->routeIs('kpi.*') ? 'active' : '' }}">
                <i class="bi bi-trophy"></i> {{ __('app.kpi_commission') }}
            </a>
        @endcan

        @if (auth()->user()->hasPermission('users.view') || auth()->user()->hasPermission('roles.view') || auth()->user()->hasPermission('settings.manage'))
            <div class="nav-section-title">{{ __('app.nav_admin') }}</div>
            @can('users.view')
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> {{ __('app.users') }}
                </a>
            @endcan
            @can('roles.view')
                <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i> {{ __('app.roles') }}
                </a>
            @endcan
            @can('settings.manage')
                <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <i class="bi bi-envelope-gear"></i> {{ __('app.notification_settings') }}
                </a>
                <a href="{{ route('admin.backups.index') }}" class="nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}">
                    <i class="bi bi-database-fill-down"></i> {{ __('app.backups') }}
                </a>
            @endcan
        @endif
    </nav>
</aside>
