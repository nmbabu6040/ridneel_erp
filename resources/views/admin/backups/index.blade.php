@extends('layouts.app')

@section('title', __('app.backups'))
@section('page-title', __('app.backups'))

@section('content')
    <div class="stat-card mb-4" style="max-width:560px;">
        <h6 class="fw-bold mb-2"><i class="bi bi-database-fill-down"></i> {{ __('app.create_backup_now') }}</h6>
        <p class="small text-secondary">{{ __('app.backup_hint') }}</p>
        <form method="POST" action="{{ route('admin.backups.store') }}">
            @csrf
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ __('app.create_backup') }}</button>
        </form>
    </div>

    <div class="stat-card p-0">
        <div class="p-3 border-bottom fw-bold small">{{ __('app.existing_backups') }}</div>
        <div class="table-responsive-card">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.file_name') }}</th>
                        <th>{{ __('app.size') }}</th>
                        <th>{{ __('app.date') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($backups as $b)
                        <tr>
                            <td><code>{{ $b['name'] }}</code></td>
                            <td>{{ number_format($b['size'] / 1024 / 1024, 2) }} MB</td>
                            <td>{{ $b['date']->format('d M Y, h:i A') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.backups.download', $b['name']) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                                <form action="{{ route('admin.backups.destroy', $b['name']) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
