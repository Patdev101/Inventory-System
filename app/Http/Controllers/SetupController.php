<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * One-time "Getting ready" setup. It only exists while the install has
 * no admin account; once the first admin is created every setup route
 * returns 404, and further accounts (managers, staff, other admins) are
 * created by an admin from User Management.
 */
class SetupController extends Controller
{
    public static function isComplete(): bool
    {
        return User::where('role', User::ROLE_ADMIN)->exists();
    }

    public function create(): View
    {
        abort_if(self::isComplete(), 404);

        return view('setup.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(self::isComplete(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        // Re-check inside a locked transaction so two simultaneous
        // submissions can't both create a "first" admin.
        $created = DB::transaction(function () use ($validated) {
            if (User::where('role', User::ROLE_ADMIN)->lockForUpdate()->exists()) {
                return false;
            }

            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]);

            return true;
        });

        abort_unless($created, 404);

        return redirect()
            ->route('login')
            ->with('status', 'Admin account created. Sign in to add your managers, staff and other admins.');
    }
}
