<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    public function test_admin_can_create_a_manager_and_a_staff_account(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get(route('users.create'))
            ->assertOk();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Manager',
            'email' => 'newmanager@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_MANAGER,
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'newmanager@example.com',
            'role' => User::ROLE_MANAGER,
        ]);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Staff',
            'email' => 'newstaff@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_STAFF,
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'newstaff@example.com',
            'role' => User::ROLE_STAFF,
        ]);
    }

    public function test_manager_can_only_create_staff_accounts(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'New Staff',
            'email' => 'staffbymanager@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_STAFF,
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'staffbymanager@example.com',
            'role' => User::ROLE_STAFF,
        ]);

        $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'Sneaky Manager',
            'email' => 'sneaky@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_MANAGER,
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'sneaky@example.com',
        ]);

        $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'Sneaky Admin',
            'email' => 'sneakyadmin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_ADMIN,
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'sneakyadmin@example.com',
        ]);
    }

    public function test_staff_cannot_access_user_management(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);

        $this->actingAs($staff)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('users.create'))
            ->assertForbidden();
    }

    public function test_manager_user_list_is_scoped_to_staff_only(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $this->makeUser(User::ROLE_ADMIN);
        $otherManager = $this->makeUser(User::ROLE_MANAGER);
        $staff = $this->makeUser(User::ROLE_STAFF);

        $response = $this->actingAs($manager)->get(route('users.index'));

        $response->assertOk();
        $response->assertSee($staff->name);
        $response->assertDontSee($otherManager->name);
    }

    public function test_admin_can_deactivate_and_reactivate_a_staff_account(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $staff = $this->makeUser(User::ROLE_STAFF);

        $this->actingAs($admin)
            ->patch(route('users.deactivate', $staff))
            ->assertRedirect(route('users.index'));

        $this->assertFalse($staff->fresh()->is_active);

        $this->actingAs($admin)
            ->patch(route('users.activate', $staff))
            ->assertRedirect(route('users.index'));

        $this->assertTrue($staff->fresh()->is_active);
    }

    public function test_manager_cannot_deactivate_another_manager_or_admin(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $otherManager = $this->makeUser(User::ROLE_MANAGER);
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($manager)
            ->patch(route('users.deactivate', $otherManager))
            ->assertForbidden();

        $this->actingAs($manager)
            ->patch(route('users.deactivate', $admin))
            ->assertForbidden();
    }

    public function test_user_cannot_deactivate_their_own_account(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->patch(route('users.deactivate', $admin))
            ->assertRedirect(route('users.index'));

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);
        $staff->update(['is_active' => false, 'password' => bcrypt('password')]);

        $this->post(route('login.store'), [
            'email' => $staff->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivating_a_logged_in_user_ends_their_session(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $staff = $this->makeUser(User::ROLE_STAFF);

        $this->actingAs($admin)->patch(route('users.deactivate', $staff));

        $this->actingAs($staff->fresh())
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_user_cannot_edit_another_users_account_through_their_own_profile(): void
    {
        $staff = $this->makeUser(User::ROLE_STAFF);
        $otherStaff = $this->makeUser(User::ROLE_STAFF);

        // Staff has no route to the admin edit form at all.
        $this->actingAs($staff)
            ->get(route('users.edit', $otherStaff))
            ->assertForbidden();

        $this->actingAs($staff)
            ->put(route('users.update', $otherStaff), [
                'name' => 'Hijacked',
                'email' => 'hijacked@example.com',
                'role' => User::ROLE_STAFF,
                'is_active' => '1',
            ])
            ->assertForbidden();

        $this->assertNotSame('hijacked@example.com', $otherStaff->fresh()->email);
    }

    public function test_manager_cannot_access_admin_only_settings_outside_user_management(): void
    {
        // Managers do have user-management access per the existing
        // hierarchy (they can manage staff), but never admin-only areas
        // like Companies/Units of Measure/Product Categories.
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $this->actingAs($manager)
            ->get(route('companies.create'))
            ->assertForbidden();
    }

    public function test_admin_can_edit_another_users_email(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $staff = $this->makeUser(User::ROLE_STAFF);

        $this->actingAs($admin)
            ->put(route('users.update', $staff), [
                'name' => $staff->name,
                'email' => 'updated-by-admin@example.com',
                'role' => User::ROLE_STAFF,
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertSame('updated-by-admin@example.com', $staff->fresh()->email);
    }

    public function test_admin_can_change_another_users_role(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $staff = $this->makeUser(User::ROLE_STAFF);

        $this->actingAs($admin)
            ->put(route('users.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => User::ROLE_MANAGER,
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertSame(User::ROLE_MANAGER, $staff->fresh()->role);
    }

    public function test_admin_can_enable_and_disable_another_user_via_edit(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $staff = $this->makeUser(User::ROLE_STAFF);

        $this->actingAs($admin)->put(route('users.update', $staff), [
            'name' => $staff->name,
            'email' => $staff->email,
            'role' => User::ROLE_STAFF,
            // is_active omitted -> false
        ]);

        $this->assertFalse($staff->fresh()->is_active);

        $this->actingAs($admin)->put(route('users.update', $staff), [
            'name' => $staff->name,
            'email' => $staff->email,
            'role' => User::ROLE_STAFF,
            'is_active' => '1',
        ]);

        $this->assertTrue($staff->fresh()->is_active);
    }

    public function test_admin_cannot_edit_or_reset_password_for_their_own_account(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get(route('users.edit', $admin))
            ->assertRedirect(route('account.edit'));

        $this->actingAs($admin)
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => 'sneaky-self-change@example.com',
                'role' => User::ROLE_MANAGER,
                'is_active' => '0',
            ])
            ->assertRedirect(route('account.edit'));

        $admin->refresh();
        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertNotSame('sneaky-self-change@example.com', $admin->email);

        $this->actingAs($admin)
            ->get(route('users.reset-password', $admin))
            ->assertRedirect(route('account.edit'));

        $this->actingAs($admin)
            ->post(route('users.reset-password.store', $admin), [
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('account.edit'));
    }

    public function test_invalid_role_is_rejected_on_edit(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $staff = $this->makeUser(User::ROLE_STAFF);

        $this->actingAs($admin)
            ->put(route('users.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => 'superuser',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('role');
    }

    public function test_admin_can_reset_another_users_password(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $staff = $this->makeUser(User::ROLE_STAFF, [
            'password' => \Illuminate\Support\Facades\Hash::make('old-password'),
        ]);

        $this->actingAs($admin)
            ->post(route('users.reset-password.store', $staff), [
                'password' => 'admin-set-password-123',
                'password_confirmation' => 'admin-set-password-123',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('admin-set-password-123', $staff->fresh()->password)
        );
    }

    public function test_admin_reset_with_require_password_change_forces_change_on_next_login(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $staff = $this->makeUser(User::ROLE_STAFF);

        $this->actingAs($admin)->post(route('users.reset-password.store', $staff), [
            'password' => 'admin-set-password-123',
            'password_confirmation' => 'admin-set-password-123',
            'require_password_change' => '1',
        ]);

        $this->assertTrue($staff->fresh()->must_change_password);
    }

    public function test_manager_cannot_reset_an_admins_password(): void
    {
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($manager)
            ->get(route('users.reset-password', $admin))
            ->assertForbidden();

        $this->actingAs($manager)
            ->post(route('users.reset-password.store', $admin), [
                'password' => 'sneaky-password-123',
                'password_confirmation' => 'sneaky-password-123',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_edit_and_reset_password_for_another_admin(): void
    {
        // Admins have full user-management access, including over other
        // admins — only their OWN account is off-limits through these
        // routes (enforced separately above).
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $otherAdmin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get(route('users.edit', $otherAdmin))
            ->assertOk();

        $this->actingAs($admin)
            ->put(route('users.update', $otherAdmin), [
                'name' => $otherAdmin->name,
                'email' => 'other-admin-updated@example.com',
                'role' => User::ROLE_ADMIN,
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertSame('other-admin-updated@example.com', $otherAdmin->fresh()->email);

        $this->actingAs($admin)
            ->post(route('users.reset-password.store', $otherAdmin), [
                'password' => 'admin-reset-admin-123',
                'password_confirmation' => 'admin-reset-admin-123',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('admin-reset-admin-123', $otherAdmin->fresh()->password)
        );
    }

    public function test_admin_can_edit_a_user_with_a_legacy_or_invalid_role_value(): void
    {
        // The role column has no DB-level enum constraint, so a row with a
        // role outside App\Models\User::ROLES can exist (e.g. inserted
        // directly, or migrated from another system). Admin must still be
        // able to open and fix that account rather than being locked out
        // entirely by an authorization check keyed off ROLES.
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $legacyUser = $this->makeUser(User::ROLE_STAFF, ['role' => 'cashier']);

        $this->actingAs($admin)
            ->get(route('users.edit', $legacyUser))
            ->assertOk()
            ->assertSee('cashier', false);

        $this->actingAs($admin)
            ->put(route('users.update', $legacyUser), [
                'name' => $legacyUser->name,
                'email' => $legacyUser->email,
                'role' => User::ROLE_STAFF,
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertSame(User::ROLE_STAFF, $legacyUser->fresh()->role);
    }

    public function test_password_hash_is_never_returned_in_responses(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $staff = $this->makeUser(User::ROLE_STAFF);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk();
        $response->assertDontSee($staff->password, false);
        $response->assertDontSee($admin->password, false);
    }
}
