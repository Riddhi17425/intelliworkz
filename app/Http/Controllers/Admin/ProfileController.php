<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:10', 'max:200', 'confirmed'],
        ]);

        $user->fill(['name' => $data['name'], 'email' => $data['email']]);
        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        ActivityLog::record('updated', 'Updated own profile' . (!empty($data['password']) ? ' and password' : ''), $user);

        return back()->with('success', 'Your profile has been updated.');
    }
}
