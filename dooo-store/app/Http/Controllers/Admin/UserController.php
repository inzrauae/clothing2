<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users', ['users' => User::query()->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', Password::defaults()],
        ]);
        $new = User::query()->create($data);
        Audit::log('user.created', ['user' => $new->email, 'role' => $new->role]);

        return back()->with('ok', "Team member {$data['name']} added. They can sign in at /admin.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['nullable', Password::defaults()],
        ]);

        if ($user->role === 'owner' && $data['role'] !== 'owner' && User::query()->where('role', 'owner')->count() <= 1) {
            return back()->withErrors(['role' => 'There must always be at least one owner.']);
        }

        $user->name = $data['name'];
        $user->role = $data['role'];
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();
        Audit::log('user.updated', ['user' => $user->email, 'role' => $user->role, 'password_changed' => ! empty($data['password'])]);

        return back()->with('ok', "{$user->name} updated.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => "You can't delete your own account."]);
        }
        if ($user->role === 'owner' && User::query()->where('role', 'owner')->count() <= 1) {
            return back()->withErrors(['user' => 'There must always be at least one owner.']);
        }
        Audit::log('user.deleted', ['user' => $user->email]);
        $user->delete();

        return back()->with('ok', "{$user->name} removed.");
    }
}
