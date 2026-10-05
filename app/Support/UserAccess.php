<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Who may see and change which company's and location's data.
 *
 * Admins are unrestricted. Staff and managers belong to one location (and
 * so one company): they work with that location's stock, orders and
 * requests, and can see the other locations of their own company only to
 * pick a transfer destination. Another company does not exist for them.
 */
class UserAccess
{
    private static array $companyCache = [];

    public static function enabled(): bool
    {
        return (bool) config('access.restrict_users_to_location');
    }

    private static int $bypass = 0;

    /**
     * Run work that legitimately crosses locations (a transfer touches the
     * other site's stock row) without the per-location filter. Only for use
     * after the request was authorised and its ids were checked.
     */
    public static function unscoped(callable $work): mixed
    {
        self::$bypass++;

        try {
            return $work();
        } finally {
            self::$bypass--;
        }
    }

    /** The signed-in user, when they are limited to a location. */
    public static function restrictedUser(): ?User
    {
        if (!self::enabled() || self::$bypass > 0) {
            return null;
        }

        $user = Auth::user();

        return $user instanceof User && !$user->isAdmin() ? $user : null;
    }

    public static function restricted(): bool
    {
        return self::restrictedUser() !== null;
    }

    public static function locationId(): ?int
    {
        $user = self::restrictedUser();

        return $user && $user->location_id ? (int) $user->location_id : null;
    }

    public static function companyId(): ?int
    {
        $user = self::restrictedUser();

        if (!$user || !$user->location_id) {
            return null;
        }

        if ($user->company_id) {
            return (int) $user->company_id;
        }

        $locationId = (int) $user->location_id;

        return self::$companyCache[$locationId] ??= (int) DB::table('locations')->where('id', $locationId)->value('company_id') ?: null;
    }

    /** A restricted user must work at their own location. */
    public static function assertLocation(int|string|null $locationId): void
    {
        if (!self::restricted()) {
            return;
        }

        abort_unless(
            self::locationId() !== null && (int) $locationId === self::locationId(),
            403,
            'You can only work with your own location.'
        );
    }

    /** A restricted user may only touch their own company. */
    public static function assertCompany(int|string|null $companyId): void
    {
        if (!self::restricted()) {
            return;
        }

        abort_unless(
            self::companyId() !== null && (int) $companyId === self::companyId(),
            403,
            'You can only work with your own company.'
        );
    }

    public static function forgetCache(): void
    {
        self::$companyCache = [];
    }
}
