@extends('layouts.guest')

@section('title', __('app.login'))

@section('content')
    <div class="text-center mb-4">
        <span class="brand-badge d-inline-flex align-items-center justify-content-center mb-2" style="width:52px;height:52px;border-radius:12px;background:var(--brand-primary);color:#fff;font-size:1.4rem;font-weight:700;">R</span>
        <h4 class="fw-bold mb-0">{{ __('app.app_name') }}</h4>
        <p class="text-secondary small mb-0">Ridneel Trade International</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 small">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('app.email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   required autofocus autocomplete="username">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('app.password') }}</label>
            <input id="password" type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="current-password">
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">{{ __('app.remember_me') }}</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-box-arrow-in-right me-1"></i> {{ __('app.login') }}
        </button>
    </form>
@endsection
