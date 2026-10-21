@extends('layouts.app')

@section('title', __('app.my_profile'))
@section('page-title', __('app.my_profile'))

@section('content')
    <div class="row g-3">
        <div class="col-md-6">
            <div class="stat-card">
                <h6 class="fw-bold mb-3"><i class="bi bi-person"></i> {{ __('app.profile_info') }}</h6>

                @if ($errors->hasAny(['name', 'email', 'phone']))
                    <div class="alert alert-danger small">
                        <ul class="mb-0 ps-3">
                            @foreach (['name', 'email', 'phone'] as $f)
                                @error($f)<li>{{ $message }}</li>@enderror
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">{{ __('app.name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('app.email') }}</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('app.phone') }}</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('app.role') }}</label>
                        <input type="text" class="form-control" value="{{ $user->role?->displayName() }}" disabled>
                        <div class="form-text">{{ __('app.role_change_hint') }}</div>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save') }}</button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card">
                <h6 class="fw-bold mb-3"><i class="bi bi-shield-lock"></i> {{ __('app.change_password') }}</h6>

                @if ($errors->hasAny(['current_password', 'password']))
                    <div class="alert alert-danger small">
                        <ul class="mb-0 ps-3">
                            @foreach (['current_password', 'password'] as $f)
                                @error($f)<li>{{ $message }}</li>@enderror
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">{{ __('app.current_password') }}</label>
                        <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('app.new_password') }}</label>
                        <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">{{ __('app.confirm_password') }}</label>
                        <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="bi bi-key"></i> {{ __('app.update_password') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
