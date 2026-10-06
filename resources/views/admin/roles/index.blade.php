@extends('layouts.app')

@section('title', __('app.roles'))
@section('page-title', __('app.roles'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">{{ __('app.roles') }}</h5>
        @can('roles.create')
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> {{ __('app.add_role') }}
            </a>
        @endcan
    </div>

    <div class="stat-card p-0">
        <div class="table-responsive-card">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.slug') }}</th>
                        <th>{{ __('app.permissions') }}</th>
                        <th>{{ __('app.users') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $role->displayName() }}</div>
                                <div class="small text-secondary">{{ $role->name }} @if($role->name_bn) / {{ $role->name_bn }} @endif</div>
                            </td>
                            <td><code>{{ $role->slug }}</code></td>
                            <td>
                                @if (in_array('*', $role->permissions ?? []))
                                    <span class="badge text-bg-primary">{{ __('app.all_permissions') }}</span>
                                @else
                                    <span class="badge text-bg-secondary">{{ count($role->permissions ?? []) }} {{ __('app.permissions') }}</span>
                                @endif
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td class="text-end">
                                @can('roles.edit')
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('roles.delete')
                                    @if (!$role->is_system)
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $roles->links() }}</div>
@endsection
