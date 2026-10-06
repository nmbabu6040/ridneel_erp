@extends('layouts.app')

@section('title', $role->exists ? __('app.edit_role') : __('app.add_role'))
@section('page-title', $role->exists ? __('app.edit_role') : __('app.add_role'))

@section('content')
    <div class="stat-card">
        <form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
            @csrf
            @if ($role->exists) @method('PUT') @endif

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.name') }} (English)</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $role->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.name') }} (বাংলা)</label>
                    <input type="text" name="name_bn" class="form-control @error('name_bn') is-invalid @enderror"
                           value="{{ old('name_bn', $role->name_bn) }}">
                    @error('name_bn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.slug') }}</label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                           value="{{ old('slug', $role->slug) }}" {{ $role->is_system ? 'readonly' : '' }} required>
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">{{ __('app.slug_hint') }}</div>
                </div>
            </div>

            @if ($role->slug === 'admin')
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-1"></i> {{ __('app.super_admin_note') }}
                </div>
            @else
                <h6 class="fw-bold mb-3">{{ __('app.permissions') }}</h6>
                <div class="row g-3 mb-3">
                    @foreach ($permissionGroups as $groupName => $permissions)
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold small mb-2">{{ $groupName }}</div>
                                @foreach ($permissions as $key => $labels)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]"
                                               value="{{ $key }}" id="perm-{{ $key }}"
                                               {{ in_array($key, old('permissions', $role->permissions ?? [])) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="perm-{{ $key }}">
                                            {{ app()->getLocale() === 'bn' ? $labels['bn'] : $labels['en'] }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> {{ __('app.save') }}
                </button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
