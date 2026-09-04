<?php

namespace App\Http\Controllers;

use App\Services\AccountAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        private readonly AccountAuditLogger $auditLogger
    ) {
    }

    public function edit(Request $request): View
    {
        return view('account.edit', [
            'user' => $request->user(),
        ]);
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'current_password' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Your current password is incorrect.'])
                ->withInput($request->except('current_password'));
        }

        $oldEmail = $user->email;
        $newEmail = $validated['email'];

        if ($newEmail === $oldEmail) {
            return back()->with('success', 'Email unchanged.');
        }

        $user->update(['email' => $newEmail]);

        $this->auditLogger->emailChangedBySelf($user, $oldEmail, $newEmail);

        return redirect()
            ->route('account.edit')
            ->with('success', 'Your email address has been updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        $this->auditLogger->passwordChangedBySelf($user);

        return redirect()
            ->route('account.edit')
            ->with('success', 'Your password has been changed.');
    }
}
