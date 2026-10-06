<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role')->latest();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.form', ['user' => new User(), 'roles' => $roles]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role_id'  => ['required', 'exists:roles,id'],
            'status'          => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $plainPassword = $data['password']; // kept only in memory, for the one-time welcome email below
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        NotificationDispatcher::send(
            eventKey: 'user_welcome',
            subject: __('app.email_welcome_subject'),
            view: 'emails.user-welcome',
            data: [
                'name'      => $user->name,
                'email'     => $user->email,
                'password'  => $plainPassword,
                'roleName'  => $user->role?->displayName() ?? '-',
                'loginUrl'  => route('login'),
            ],
            defaultRecipients: [$user->email]
        );

        return redirect()->route('admin.users.index')
            ->with('success', __('app.user_created'));
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.form', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role_id'  => ['required', 'exists:roles,id'],
            'status'          => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'password'        => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', __('app.user_updated'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', __('app.cannot_delete_self'));
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', __('app.user_deleted'));
    }
}
