<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
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

        return redirect()
            ->route('users.index')
            ->with('success', 'User account deactivated.');
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManageable($request->user(), $user);

        $user->update(['is_active' => true]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User account activated.');
    }

    /**
     * Ensure the acting user is allowed to activate/deactivate the
     * target account, using the same hierarchy as account creation:
     * admins manage managers and staff, managers manage staff only.
     */
    private function authorizeManageable(User $actingUser, User $target): void
    {
        abort_unless(
            in_array($target->role, $this->assignableRoles($actingUser), true),
            403
        );
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
