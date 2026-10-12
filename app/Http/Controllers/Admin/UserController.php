<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\Search;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Administrator-only (see the "manage-users" gate on the routes). */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = Search::like($request->string('q')->toString());
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $user = new User();
        $user->role = User::ROLE_EDITOR;
        $user->is_active = true;
        return view('admin.users.form', compact('user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $user = new User(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
        $user->role = $data['role'];
        $user->is_active = $request->boolean('is_active');
        $user->save();

        ActivityLog::record('created', "Created user {$user->email}", $user);
        return redirect()->route('admin.users.index')->with('success', "User {$user->name} was created.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate($this->rules($user));

        // never let the last active administrator lock everyone out
        $demoting = $user->isAdmin() && ($data['role'] !== User::ROLE_ADMIN || !$request->boolean('is_active'));
        if ($demoting && $this->activeAdmins()->whereKeyNot($user->id)->doesntExist()) {
            return back()->withInput()->with('error', 'There must always be at least one active administrator.');
        }

        $user->fill(['name' => $data['name'], 'email' => $data['email']]);
        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->role = $data['role'];
        $user->is_active = $request->boolean('is_active');
        $user->save();

        ActivityLog::record('updated', "Updated user {$user->email}", $user);
        return redirect()->route('admin.users.index')->with('success', "User {$user->name} was updated.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account while signed in.');
        }
        if ($user->isAdmin() && $this->activeAdmins()->whereKeyNot($user->id)->doesntExist()) {
            return back()->with('error', 'There must always be at least one active administrator.');
        }

        $user->delete();
        ActivityLog::record('deleted', "Deleted user {$user->email}");
        return redirect()->route('admin.users.index')->with('success', 'The user was deleted.');
    }

    private function activeAdmins()
    {
        return User::where('role', User::ROLE_ADMIN)->where('is_active', true);
    }

    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:10', 'max:200', 'confirmed'],
        ];
    }
}
