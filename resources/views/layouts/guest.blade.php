<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('app.app_name'))</title>

    <script>
        (function () {
            try {
                var saved = localStorage.getItem('dms-theme');
                if (saved === 'dark' || saved === 'light') {
                    document.documentElement.setAttribute('data-bs-theme', saved);
                }
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>

<div class="position-absolute top-0 end-0 p-3 d-flex gap-2">
    <div class="dropdown">
        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-translate"></i> {{ app()->getLocale() === 'bn' ? 'বাংলা' : 'English' }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="{{ route('language.switch', 'bn') }}">বাংলা</a></li>
            <li><a class="dropdown-item" href="{{ route('language.switch', 'en') }}">English</a></li>
        </ul>
    </div>
    <button class="btn btn-sm btn-light" type="button" data-theme-toggle>
        <i class="bi bi-moon-stars-fill" data-theme-icon></i>
    </button>
</div>

<div class="auth-wrapper">
    <div class="card auth-card">
        <div class="card-body p-4 p-md-5">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
