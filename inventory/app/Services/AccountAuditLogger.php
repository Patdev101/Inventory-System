<?php

namespace App\Services;

use App\Models\AccountAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Lightweight structured logging for account-management events, mirroring
 * the POS app's PosAuditLogger. No password value (plaintext or hashed)
 * is ever included in these entries.
 */
class AccountAuditLogger
{
    protected function record(string $event, array $context, ?int $userId = null, ?string $subjectType = null, ?int $subjectId = null): void
    {
        Log::info($event, $context);

        AccountAuditLog::create([
            'event' => $event,
            'user_id' => $userId,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'context' => $context,
        ]);
    }

    public function emailChangedBySelf(User $user, string $oldEmail, string $newEmail): void
    {
        $this->record('account.email.changed_by_self', [
            'user_id' => $user->id,
            'old_email' => $oldEmail,
            'new_email' => $newEmail,
        ], $user->id, User::class, $user->id);
    }

    public function nameChangedBySelf(User $user, string $oldName, string $newName): void
    {
        $this->record('account.name.changed_by_self', [
            'user_id' => $user->id,
            'old_name' => $oldName,
            'new_name' => $newName,
        ], $user->id, User::class, $user->id);
    }

    public function passwordChangedBySelf(User $user): void
    {
        $this->record('account.password.changed_by_self', [
            'user_id' => $user->id,
            'email' => $user->email,
        ], $user->id, User::class, $user->id);
    }

    public function emailChangedByAdmin(User $admin, User $target, string $oldEmail, string $newEmail): void
    {
        $this->record('account.email.changed_by_admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'target_user_id' => $target->id,
            'old_email' => $oldEmail,
            'new_email' => $newEmail,
        ], $admin->id, User::class, $target->id);
    }

    public function roleChangedByAdmin(User $admin, User $target, string $oldRole, string $newRole): void
    {
        $this->record('account.role.changed_by_admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'target_user_id' => $target->id,
            'target_email' => $target->email,
            'old_role' => $oldRole,
            'new_role' => $newRole,
        ], $admin->id, User::class, $target->id);
    }

    public function statusChangedByAdmin(User $admin, User $target, bool $isActive): void
    {
        $this->record('account.status.changed_by_admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'target_user_id' => $target->id,
            'target_email' => $target->email,
            'is_active' => $isActive,
        ], $admin->id, User::class, $target->id);
    }

    public function passwordResetByAdmin(User $admin, User $target, bool $mustChangePassword): void
    {
        $this->record('account.password.reset_by_admin', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'target_user_id' => $target->id,
            'target_email' => $target->email,
            'must_change_password' => $mustChangePassword,
        ], $admin->id, User::class, $target->id);
    }

    public function passwordResetViaEmailLink(User $user): void
    {
        $this->record('account.password.reset_via_email_link', [
            'user_id' => $user->id,
            'email' => $user->email,
        ], $user->id, User::class, $user->id);
    }
}
