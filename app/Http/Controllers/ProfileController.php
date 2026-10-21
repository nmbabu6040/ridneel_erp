<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Deliberately its own controller (not folded into Admin\UserController)
     * because the authorization model is different in kind, not just
     * degree: every route here operates on $request->user() — the
     * currently authenticated person — never on a {user} route
     * parameter. There is no "edit someone else's profile" path through
     * this controller, so no permission check is needed beyond 'auth';
     * mixing that with UserController (which DOES take a {user} and DOES
     * need users.edit) would make it easy to accidentally blur the two.
     */
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($data);

        return back()->with('success', __('app.profile_updated'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('app.current_password_incorrect'),
            ]);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', __('app.password_updated'));
    }
}
