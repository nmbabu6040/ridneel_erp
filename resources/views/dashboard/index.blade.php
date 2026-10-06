@extends('layouts.app')

@section('title', __('app.dashboard'))
@section('page-title', __('app.dashboard'))

@section('content')
    <div class="mb-4">
        <h4 class="fw-bold mb-1">{{ __('app.welcome') }}, {{ $user->name }} 👋</h4>
        <p class="text-secondary mb-0">{{ $user->role?->displayName() ?? __('app.no_role') }}</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="text-secondary small">{{ __('app.total_users') }}</div>
                        <div class="fs-4 fw-bold">{{ $stats['total_users'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div>
                        <div class="text-secondary small">{{ __('app.active_users') }}</div>
                        <div class="fs-4 fw-bold">{{ $stats['active_users'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <div class="text-secondary small">{{ __('app.total_roles') }}</div>
                        <div class="fs-4 fw-bold">{{ $stats['total_roles'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
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
                <div class="stat-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-receipt"></i></div>
                        <div>
                            <div class="text-secondary small">{{ __('app.today_invoices') }}</div>
                            <div class="fs-4 fw-bold">{{ $stats['today_sales_count'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-cash-stack"></i></div>
                        <div>
                            <div class="text-secondary small">{{ __('app.today_sales_total') }}</div>
                            <div class="fs-5 fw-bold">{{ number_format($stats['today_sales_total'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-exclamation-circle"></i></div>
                        <div>
                            <div class="text-secondary small">{{ __('app.total_due') }}</div>
                            <div class="fs-5 fw-bold">{{ number_format($stats['total_due'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (isset($stats['month_expenses']))
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-receipt-cutoff"></i></div>
                        <div>
                            <div class="text-secondary small">{{ __('app.this_month_expenses') }}</div>
                            <div class="fs-5 fw-bold">{{ number_format($stats['month_expenses'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-cash-coin"></i></div>
                        <div>
                            <div class="text-secondary small">{{ __('app.total_payable') }}</div>
                            <div class="fs-5 fw-bold">{{ number_format($stats['total_payable'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (isset($stats['total_sr']))
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-person-check-fill"></i></div>
                        <div>
                            <div class="text-secondary small">{{ __('app.sr_checked_in_today') }}</div>
                            <div class="fs-4 fw-bold">{{ $stats['sr_checked_in'] }} / {{ $stats['total_sr'] }}</div>
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
                    <div class="stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-box-seam-fill"></i></div>
                            <div>
                                <div class="text-secondary small">{{ __('app.products') }}</div>
                                <div class="fs-4 fw-bold">{{ $stats['total_products'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-exclamation-triangle-fill"></i></div>
                            <div>
                                <div class="text-secondary small">{{ __('app.low_stock_only') }}</div>
                                <div class="fs-4 fw-bold">{{ $stats['low_stock_count'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if (isset($stats['total_dealers']))
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-shop"></i></div>
                            <div>
                                <div class="text-secondary small">{{ __('app.dealers') }}</div>
                                <div class="fs-4 fw-bold">{{ $stats['total_dealers'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-people-fill"></i></div>
                            <div>
                                <div class="text-secondary small">{{ __('app.retailers') }}</div>
                                <div class="fs-4 fw-bold">{{ $stats['total_retailers'] }}</div>
                            </div>
                        </div>
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
