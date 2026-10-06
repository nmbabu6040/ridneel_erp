@extends('layouts.app')

@section('title', __('app.users'))
@section('page-title', __('app.users'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">{{ __('app.users') }}</h5>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                       placeholder="{{ __('app.search_placeholder') }}">
            </form>
            @can('users.create')
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm text-nowrap">
                    <i class="bi bi-plus-lg"></i> {{ __('app.add_user') }}
                </a>
            @endcan
        </div>
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.email') }}</th>
                        <th>{{ __('app.phone') }}</th>
                        <th>{{ __('app.role') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge rounded-circle bg-primary" style="width:1.8rem;height:1.8rem;line-height:1.5rem;">{{ $u->initials() }}</span>
                                    {{ $u->name }}
                                </div>
                            </td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->phone ?: '—' }}</td>
                            <td>{{ $u->role?->displayName() ?? '—' }}</td>
                            <td>
                                <span class="badge badge-status-{{ $u->status }}">{{ __('app.status_' . $u->status) }}</span>
                            </td>
                            <td class="text-end">
                                @can('users.edit')
                                    <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('users.delete')
                                    @if ($u->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
@endsection
