<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AccountAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly AccountAuditLogger $auditLogger
    ) {
    }

    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->user()->isManager()) {
            $query->where('role', User::ROLE_STAFF);
        }

        $users = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(Request $request): View
    {
        $assignableRoles = $this->assignableRoles($request->user());

        return view('users.create', compact('assignableRoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $assignableRoles = $this->assignableRoles($request->user());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:' . implode(',', $assignableRoles)],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User account created successfully.');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        $this->authorizeManageable($request->user(), $user);

        $user->update(['is_active' => false]);

        $this->auditLogger->statusChangedByAdmin($request->user(), $user, false);

        return redirect()
            ->route('users.index')
            ->with('success', 'User account deactivated.');
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManageable($request->user(), $user);

        $user->update(['is_active' => true]);

        $this->auditLogger->statusChangedByAdmin($request->user(), $user, true);

        return redirect()
            ->route('users.index')
            ->with('success', 'User account activated.');
    }

    public function edit(Request $request, User $user): RedirectResponse|View
    {
        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('account.edit')
                ->with('error', 'Use "My Account" to edit your own profile.');
        }

        $this->authorizeManageable($request->user(), $user);

        $assignableRoles = $this->roleOptionsForEdit($request->user(), $user);

        return view('users.edit', compact('user', 'assignableRoles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('account.edit')
                ->with('error', 'Use "My Account" to edit your own profile.');
        }

        $this->authorizeManageable($request->user(), $user);

        $assignableRoles = $this->roleOptionsForEdit($request->user(), $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'role' => ['required', 'string', 'in:' . implode(',', $assignableRoles)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldEmail = $user->email;
        $oldRole = $user->role;
        $oldIsActive = (bool) $user->is_active;
        $newEmail = $validated['email'];
        $newRole = $validated['role'];
        $newIsActive = $request->boolean('is_active');

        $user->update([
            'name' => $validated['name'],
            'email' => $newEmail,
            'role' => $newRole,
            'is_active' => $newIsActive,
        ]);

        if ($newEmail !== $oldEmail) {
            $this->auditLogger->emailChangedByAdmin($request->user(), $user, $oldEmail, $newEmail);
        }

        if ($newRole !== $oldRole) {
            $this->auditLogger->roleChangedByAdmin($request->user(), $user, $oldRole, $newRole);
        }

        if ($newIsActive !== $oldIsActive) {
            $this->auditLogger->statusChangedByAdmin($request->user(), $user, $newIsActive);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User account updated.');
    }

    public function showResetPassword(Request $request, User $user): RedirectResponse|View
    {
        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('account.edit')
                ->with('error', 'Use "My Account" to change your own password.');
        }

        $this->authorizeManageable($request->user(), $user);

        return view('users.reset-password', compact('user'));
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('account.edit')
                ->with('error', 'Use "My Account" to change your own password.');
        }

        $this->authorizeManageable($request->user(), $user);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
            'require_password_change' => ['nullable', 'boolean'],
        ]);

        $mustChangePassword = $request->boolean('require_password_change');

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => $mustChangePassword,
        ]);

        $this->auditLogger->passwordResetByAdmin($request->user(), $user, $mustChangePassword);

        return redirect()
            ->route('users.index')
            ->with('success', 'Password reset for ' . $user->name . '.');
    }

    /**
     * Ensure the acting user is allowed to manage (edit/reset password/
     * activate/deactivate) the target account. Admins have full user
     * management access, including over other admins — the only
     * restriction on an admin is the separate self-target guards above
     * (an admin can't act on their own account through these routes).
     * Managers may only manage staff.
     */
    private function authorizeManageable(User $actingUser, User $target): void
    {
        abort_unless($this->canManage($actingUser, $target), 403);
    }

    private function canManage(User $actingUser, User $target): bool
    {
        if ($actingUser->isAdmin()) {
            return true;
        }

        if ($actingUser->isManager()) {
            return $target->role === User::ROLE_STAFF;
        }

        return false;
    }

    /**
     * Role options for the edit form: the roles the acting user could
     * newly assign, plus the target's current role even if it's not one
     * of those (e.g. editing an existing admin, or a legacy/invalid role
     * value that predates this app's own role list) — so editing a user
     * never forces an unrelated role change just to save the form.
     */
    private function roleOptionsForEdit(User $actingUser, User $target): array
    {
        $roles = $this->assignableRoles($actingUser);

        if (!in_array($target->role, $roles, true)) {
            $roles[] = $target->role;
        }

        return $roles;
    }

    /**
     * Roles the acting user is allowed to assign when creating an
     * account. Admins may create managers and staff; managers may
     * only create staff. This is the server-side source of truth —
     * the create form's role options are driven by the same list.
     */
    private function assignableRoles(User $actingUser): array
    {
        if ($actingUser->isAdmin()) {
            return [User::ROLE_MANAGER, User::ROLE_STAFF];
        }

        if ($actingUser->isManager()) {
            return [User::ROLE_STAFF];
        }

        return [];
    }
}
