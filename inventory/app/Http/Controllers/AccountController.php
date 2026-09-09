<?php

namespace App\Http\Controllers;

use App\Services\AccountAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

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

    public function updateName(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);

        $oldName = $user->name;
        $newName = trim($validated['name']);

        if ($newName === $oldName) {
            return back()->with('success', 'Name unchanged.');
        }

        $user->update(['name' => $newName]);

        $this->auditLogger->nameChangedBySelf($user, $oldName, $newName);

        return redirect()
            ->route('account.edit')
            ->with('success', 'Your name has been updated.');
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

    /**
     * Send a one-off test email to the current user's own address, to
     * verify the configured mail driver (config/mail.php, MAIL_* in
     * .env) is actually able to deliver mail. Admin only, since it
     * exercises live mail configuration rather than app data.
     */
    public function sendTestEmail(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $user = $request->user();

        try {
            Mail::raw(
                'This is a test email from ' . config('app.name') . '. '
                . 'If you received this, your mail configuration is working correctly.',
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Test email — ' . config('app.name'));
                }
            );
        } catch (Throwable $e) {
            return back()->with(
                'error',
                'Could not send test email: ' . $e->getMessage()
            );
        }

        $driver = config('mail.default');

        return back()->with(
            'success',
            $driver === 'log'
                ? 'Test email "sent" — since MAIL_MAILER is set to "log", check storage/logs/laravel.log instead of your inbox.'
                : 'Test email sent to ' . $user->email . '. Check your inbox (and spam folder).'
        );
    }
}
