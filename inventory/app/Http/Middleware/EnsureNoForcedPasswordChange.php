<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * When an admin resets a user's password with "require password change on
 * next login", the user is locked out of the rest of the app until they
 * set their own new password from the Account page.
 */
class EnsureNoForcedPasswordChange
{
    private const ALLOWED_ROUTES = [
        'account.edit',
        'account.email.update',
        'account.password.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->must_change_password
            && !$request->routeIs(...self::ALLOWED_ROUTES)
        ) {
            return redirect()
                ->route('account.edit')
                ->with('error', 'You must set a new password before continuing.');
        }

        return $next($request);
    }
}
