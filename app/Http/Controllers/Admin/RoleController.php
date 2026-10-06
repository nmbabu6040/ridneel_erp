<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('name')->paginate(15);

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissionGroups = Permissions::all();

        return view('admin.roles.form', [
            'role'             => new Role(['permissions' => []]),
            'permissionGroups' => $permissionGroups,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Role::create($data);

        return redirect()->route('admin.roles.index')
            ->with('success', __('app.role_created'));
    }

    public function edit(Role $role)
    {
        $permissionGroups = Permissions::all();

        return view('admin.roles.form', compact('role', 'permissionGroups'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validated($request, $role->id);

        // Super Admin's "all permissions" flag is protected from edits
        // that would accidentally lock everyone out of the system.
        if ($role->slug === 'admin') {
            $data['permissions'] = ['*'];
        }

        $role->update($data);

        return redirect()->route('admin.roles.index')
            ->with('success', __('app.role_updated'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->with('error', __('app.system_role_protected'));
        }

        if ($role->users()->exists()) {
            return back()->with('error', __('app.role_in_use'));
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', __('app.role_deleted'));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'name_bn'       => ['nullable', 'string', 'max:100'],
            'slug'          => [
                'required', 'string', 'max:50', 'alpha_dash',
                Rule::unique('roles', 'slug')->ignore($ignoreId),
            ],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_merge(Permissions::keys(), ['*']))],
        ]);

        $validated['permissions'] = $validated['permissions'] ?? [];

        return $validated;
    }
}
