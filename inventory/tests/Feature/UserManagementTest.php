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
}
