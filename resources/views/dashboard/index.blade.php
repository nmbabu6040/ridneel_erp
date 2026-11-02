@extends('layouts.app')

@section('title', __('app.dashboard'))
@section('page-title', __('app.dashboard'))

@section('content')
    @php
        $hour = now()->hour;
        $greetingKey = $hour < 12 ? 'greeting_morning' : ($hour < 17 ? 'greeting_afternoon' : ($hour < 20 ? 'greeting_evening' : 'greeting_night'));
        $cur = $companySettings->currency_symbol;
    @endphp

    {{-- Animated gradient welcome banner --}}
    <div class="welcome-banner mb-4">
        <div class="welcome-banner-glow"></div>
        <div class="position-relative">
            <div class="small text-white-50 mb-1">{{ now()->translatedFormat('l, d F Y') }}</div>
            <h3 class="fw-bold text-white mb-1">{{ __('app.' . $greetingKey) }}, {{ $user->name }} 👋</h3>
            <p class="text-white-50 mb-0">{{ $user->role?->displayName() ?? __('app.no_role') }} · {{ $companySettings->displayName() }}</p>
        </div>
    </div>

    {{-- All stat cards: count-up animated, with hover lift. Each card
         declares data-count so one shared script (see @push below)
         animates every number the same way without per-card JS. --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card stat-animate accent-primary">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <div class="text-secondary small">{{ __('app.total_users') }}</div>
                        <div class="fs-4 fw-bold" data-count="{{ $stats['total_users'] }}">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card stat-animate accent-success">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-person-check-fill"></i></div>
                    <div>
                        <div class="text-secondary small">{{ __('app.active_users') }}</div>
                        <div class="fs-4 fw-bold" data-count="{{ $stats['active_users'] }}">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card stat-animate accent-warning">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-shield-lock-fill"></i></div>
                    <div>
                        <div class="text-secondary small">{{ __('app.total_roles') }}</div>
                        <div class="fs-4 fw-bold" data-count="{{ $stats['total_roles'] }}">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card stat-animate accent-info">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-calendar-check-fill"></i></div>
                    <div>
                        <div class="text-secondary small">{{ __('app.today') }}</div>
                        <div class="fs-6 fw-bold">{{ now()->translatedFormat('d M, Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (isset($stats['today_sales_count']))
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card stat-animate accent-success">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-receipt"></i></div>
                        <div>
                            <div class="text-secondary small">{{ __('app.today_invoices') }}</div>
                            <div class="fs-4 fw-bold" data-count="{{ $stats['today_sales_count'] }}">0</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card stat-animate accent-primary">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-cash-stack"></i></div>
                        <div>
                            <div class="text-secondary small">{{ __('app.today_sales_total') }}</div>
                            <div class="fs-5 fw-bold">{{ $cur }} <span data-count="{{ $stats['today_sales_total'] }}" data-decimals="2">0</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card stat-animate accent-danger">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-exclamation-circle"></i></div>
                        <div>
                            <div class="text-secondary small">{{ __('app.total_due') }}</div>
                            <div class="fs-5 fw-bold">{{ $cur }} <span data-count="{{ $stats['total_due'] }}" data-decimals="2">0</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (isset($stats['month_expenses']))
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card stat-animate accent-danger">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-receipt-cutoff"></i></div>
                        <div>
                            <div class="text-secondary small">{{ __('app.this_month_expenses') }}</div>
                            <div class="fs-5 fw-bold">{{ $cur }} <span data-count="{{ $stats['month_expenses'] }}" data-decimals="2">0</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card stat-animate accent-warning">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-cash-coin"></i></div>
                        <div>
                            <div class="text-secondary small">{{ __('app.total_payable') }}</div>
                            <div class="fs-5 fw-bold">{{ $cur }} <span data-count="{{ $stats['total_payable'] }}" data-decimals="2">0</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (isset($stats['total_sr']))
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card stat-animate accent-success">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-person-check-fill"></i></div>
                        <div>
                            <div class="text-secondary small">{{ __('app.sr_checked_in_today') }}</div>
                            <div class="fs-4 fw-bold"><span data-count="{{ $stats['sr_checked_in'] }}">0</span> / {{ $stats['total_sr'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (isset($stats['total_products']) || isset($stats['total_dealers']))
        <div class="row g-3 mb-4">
            @if (isset($stats['total_products']))
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card stat-animate accent-primary">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-box-seam-fill"></i></div>
                            <div>
                                <div class="text-secondary small">{{ __('app.products') }}</div>
                                <div class="fs-4 fw-bold" data-count="{{ $stats['total_products'] }}">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card stat-animate accent-danger">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-exclamation-triangle-fill"></i></div>
                            <div>
                                <div class="text-secondary small">{{ __('app.low_stock_only') }}</div>
                                <div class="fs-4 fw-bold" data-count="{{ $stats['low_stock_count'] }}">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if (isset($stats['total_dealers']))
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card stat-animate accent-success">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-shop"></i></div>
                            <div>
                                <div class="text-secondary small">{{ __('app.dealers') }}</div>
                                <div class="fs-4 fw-bold" data-count="{{ $stats['total_dealers'] }}">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card stat-animate accent-info">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-people-fill"></i></div>
                            <div>
                                <div class="text-secondary small">{{ __('app.retailers') }}</div>
                                <div class="fs-4 fw-bold" data-count="{{ $stats['total_retailers'] }}">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Charts --}}
    @if (!empty($charts))
        <div class="row g-3 mb-4">
            @if (isset($charts['sales_trend']))
                <div class="col-lg-8">
                    <div class="stat-card chart-card h-100">
                        <h6 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow text-primary"></i> {{ __('app.sales_trend_title') }}</h6>
                        <canvas id="chart-sales-trend" height="110"></canvas>
                    </div>
                </div>
            @endif
            @if (isset($charts['stock_status']))
                <div class="col-lg-4">
                    <div class="stat-card chart-card h-100">
                        <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-success"></i> {{ __('app.stock_status_title') }}</h6>
                        <canvas id="chart-stock-status" height="200"></canvas>
                    </div>
                </div>
            @endif
        </div>

        <div class="row g-3 mb-4">
            @if (isset($charts['top_products']) && count($charts['top_products']['labels']))
                <div class="col-lg-6">
                    <div class="stat-card chart-card h-100">
                        <h6 class="fw-bold mb-3"><i class="bi bi-trophy-fill text-warning"></i> {{ __('app.top_products_title') }}</h6>
                        <canvas id="chart-top-products" height="180"></canvas>
                    </div>
                </div>
            @endif
            @if (isset($charts['expense_breakdown']) && $charts['expense_breakdown']->count())
                <div class="col-lg-6">
                    <div class="stat-card chart-card h-100">
                        <h6 class="fw-bold mb-3"><i class="bi bi-receipt-cutoff text-danger"></i> {{ __('app.expense_breakdown_title') }}</h6>
                        <canvas id="chart-expenses" height="180"></canvas>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="stat-card">
        <h6 class="fw-bold mb-2"><i class="bi bi-signpost-2 me-1"></i> {{ __('app.roadmap_title') }}</h6>
        <p class="text-secondary small mb-3">{{ __('app.roadmap_desc') }}</p>
        <div class="row g-2 small">
            <div class="col-md-6"><i class="bi bi-check-circle-fill text-success me-1"></i> Phase 1 — {{ __('app.phase1') }}</div>
            <div class="col-md-6"><i class="bi bi-check-circle-fill text-success me-1"></i> Phase 2 — {{ __('app.phase2') }}</div>
            <div class="col-md-6"><i class="bi bi-check-circle-fill text-success me-1"></i> Phase 3 — {{ __('app.phase3') }}</div>
            <div class="col-md-6"><i class="bi bi-check-circle-fill text-success me-1"></i> Phase 4 — {{ __('app.phase4') }}</div>
            <div class="col-md-6"><i class="bi bi-check-circle-fill text-success me-1"></i> Phase 5 — {{ __('app.phase5') }}</div>
            <div class="col-md-6"><i class="bi bi-check-circle-fill text-success me-1"></i> Phase 6 — {{ __('app.phase6') }}</div>
            <div class="col-md-6"><i class="bi bi-check-circle-fill text-success me-1"></i> Phase 7 — {{ __('app.phase7') }}</div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    // ---- Count-up animation for every [data-count] element ----
    document.querySelectorAll('[data-count]').forEach(function (el) {
        var target = parseFloat(el.dataset.count) || 0;
        var decimals = parseInt(el.dataset.decimals || '0', 10);
        var duration = 900;
        var start = null;

        function step(ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            var value = target * eased;
            el.textContent = decimals > 0 ? value.toFixed(decimals) : Math.round(value).toLocaleString();
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = decimals > 0 ? target.toFixed(decimals) : Math.round(target).toLocaleString();
        }
        requestAnimationFrame(step);
    });

    // ---- Theme-aware chart colors (follows dark mode) ----
    var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    var gridColor = isDark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)';
    var textColor = isDark ? '#9aa4b2' : '#6b7280';
    Chart.defaults.color = textColor;
    Chart.defaults.font.family = "'Hind Siliguri', system-ui, sans-serif";

    @if (isset($charts['sales_trend']))
    new Chart(document.getElementById('chart-sales-trend'), {
        type: 'line',
        data: {
            labels: @json($charts['sales_trend']['labels']),
            datasets: [
                {
                    label: '{{ __("app.primary_sales") }}',
                    data: @json($charts['sales_trend']['primary']),
                    borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.12)',
                    tension: .35, fill: true, pointRadius: 2,
                },
                {
                    label: '{{ __("app.secondary_sales") }}',
                    data: @json($charts['sales_trend']['secondary']),
                    borderColor: '#198754', backgroundColor: 'rgba(25,135,84,.12)',
                    tension: .35, fill: true, pointRadius: 2,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                x: { grid: { color: gridColor } },
                y: { grid: { color: gridColor }, beginAtZero: true },
            },
        },
    });
    @endif

    @if (isset($charts['stock_status']))
    new Chart(document.getElementById('chart-stock-status'), {
        type: 'doughnut',
        data: {
            labels: ['{{ __("app.in_stock") }}', '{{ __("app.low_stock_only") }}', '{{ __("app.out_of_stock") }}'],
            datasets: [{
                data: [{{ $charts['stock_status']['in_stock'] }}, {{ $charts['stock_status']['low_stock'] }}, {{ $charts['stock_status']['out_stock'] }}],
                backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                borderWidth: 0,
            }],
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } }, cutout: '65%' },
    });
    @endif

    @if (isset($charts['top_products']) && count($charts['top_products']['labels']))
    new Chart(document.getElementById('chart-top-products'), {
        type: 'bar',
        data: {
            labels: @json($charts['top_products']['labels']),
            datasets: [{
                label: '{{ __("app.quantity") }}',
                data: @json($charts['top_products']['data']),
                backgroundColor: '#0d6efd',
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true, indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { grid: { color: gridColor }, beginAtZero: true }, y: { grid: { display: false } } },
        },
    });
    @endif

    @if (isset($charts['expense_breakdown']) && $charts['expense_breakdown']->count())
    new Chart(document.getElementById('chart-expenses'), {
        type: 'pie',
        data: {
            labels: @json($charts['expense_breakdown']->pluck('label')),
            datasets: [{
                data: @json($charts['expense_breakdown']->pluck('total')),
                backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#20c997'],
                borderWidth: 0,
            }],
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
    });
    @endif
})();
</script>
@endpush
