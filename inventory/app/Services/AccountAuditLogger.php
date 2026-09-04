<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Lightweight structured logging for account-management events, mirroring
 * the POS app's PosAuditLogger. No password value (plaintext or hashed)
 * is ever included in these entries.
 */
class AccountAuditLogger
{
    public function emailChangedBySelf(User $user, string $oldEmail, string $newEmail): void
    {
        Log::info('account.email.changed_by_self', [
            'user_id' => $user->id,
            'old_email' => $oldEmail,
            'new_email' => $newEmail,
        ]);
    }

    public function passwordChangedBySelf(User $user): void
    {
        Log::info('account.password.changed_by_self', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function emailChangedByAdmin(User $admin, User $target, string $oldEmail, string $newEmail): void
    {
        Log::info('account.email.changed_by_admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'target_user_id' => $target->id,
            'old_email' => $oldEmail,
            'new_email' => $newEmail,
        ]);
    }

    public function roleChangedByAdmin(User $admin, User $target, string $oldRole, string $newRole): void
    {
        Log::info('account.role.changed_by_admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'target_user_id' => $target->id,
            'target_email' => $target->email,
            'old_role' => $oldRole,
            'new_role' => $newRole,
        ]);
    }

    public function statusChangedByAdmin(User $admin, User $target, bool $isActive): void
    {
        Log::info('account.status.changed_by_admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'target_user_id' => $target->id,
            'target_email' => $target->email,
            'is_active' => $isActive,
        ]);
    }

    public function passwordResetByAdmin(User $admin, User $target, bool $mustChangePassword): void
    {
        Log::info('account.password.reset_by_admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'target_user_id' => $target->id,
            'target_email' => $target->email,
            'must_change_password' => $mustChangePassword,
        ]);
    }
}
