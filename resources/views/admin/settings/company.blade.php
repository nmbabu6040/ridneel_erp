@extends('layouts.app')

@section('title', __('app.company_settings'))
@section('page-title', __('app.company_settings'))

@section('content')
    <div class="stat-card" style="max-width:640px;">
        <p class="small text-secondary">{{ __('app.company_settings_hint') }}</p>

        <form method="POST" action="{{ route('admin.settings.company.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.company_name') }} (English)</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $settings->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.company_name') }} (বাংলা)</label>
                    <input type="text" name="name_bn" class="form-control"
                        value="{{ old('name_bn', $settings->name_bn) }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('app.tagline') }}</label>
                <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $settings->tagline) }}"
                    placeholder="{{ __('app.tagline_placeholder') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('app.company_logo') }}</label>
                <div class="d-flex align-items-center gap-3">
                    @if ($settings->logo_path)
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt=""
                            style="width:52px;height:52px;border-radius:10px;object-fit:cover;" class="border">
                    @endif
                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror"
                        accept="image/*">
                </div>
                <div class="form-text">{{ __('app.company_logo_hint') }}</div>
                @error('logo')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.phone') }}</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $settings->phone) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.email') }}</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $settings->email) }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.website') }}</label>
                    <input type="text" name="website" class="form-control"
                        value="{{ old('website', $settings->website) }}" placeholder="example.com">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('app.address') }}</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $settings->address) }}">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.currency_symbol') }}</label>
                    <input type="text" name="currency_symbol"
                        class="form-control @error('currency_symbol') is-invalid @enderror"
                        value="{{ old('currency_symbol', $settings->currency_symbol) }}" required>
                    <div class="form-text">{{ __('app.currency_symbol_hint') }}</div>
                    @error('currency_symbol')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('app.currency_code') }}</label>
                    <input type="text" name="currency_code"
                        class="form-control @error('currency_code') is-invalid @enderror"
                        value="{{ old('currency_code', $settings->currency_code) }}" required
                        placeholder="BDT / USD / INR">
                    @error('currency_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                <div class="mb-3">
                    <label class="form-label">
                        {{ __('app.favicon_emoji') }}
                    </label>

                    <div class="d-flex align-items-center gap-3">
                        @if ($settings->favicon_emoji)
                            <img src="{{ asset('storage/' . $settings->favicon_emoji) }}" alt="Company favicon"
                                style="width:48px;height:48px;object-fit:contain;" class="border rounded p-1">
                        @endif

                        <input type="file" name="favicon_emoji"
                            class="form-control @error('favicon_emoji') is-invalid @enderror"
                            accept="image/png,image/jpeg,image/webp,image/x-icon">
                    </div>

                    <div class="form-text">
                        Upload PNG, JPG, WEBP or ICO image. Maximum 512 KB.
                    </div>

                    @error('favicon_emoji')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>


                {{-- <div class="col-md-4">
                    <label class="form-label">{{ __('app.favicon_emoji') }}</label>
                    <input type="text" name="favicon_emoji" class="form-control"
                        value="{{ old('favicon_emoji', $settings->favicon_emoji) }}" maxlength="8">
                    <div class="form-text">{{ __('app.favicon_emoji_hint') }}</div>
                </div> --}}
            </div>

            <div class="mb-4">
                <label class="form-label">{{ __('app.invoice_footer_note') }}</label>
                <textarea name="invoice_footer_note" class="form-control" rows="2"
                    placeholder="{{ __('app.invoice_footer_note_placeholder') }}">{{ old('invoice_footer_note', $settings->invoice_footer_note) }}</textarea>
                <div class="form-text">{{ __('app.invoice_footer_note_hint') }}</div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('app.save') }}</button>
        </form>
    </div>
@endsection
