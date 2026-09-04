<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * This is a local/internal system with no mail service wired up to
 * deliver reset links, so there is no email-based password reset flow.
 * Passwords are always reset by an admin from User Management
 * (see UserController::resetPassword).
 */
class PasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }
}
