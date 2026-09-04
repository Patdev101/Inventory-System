<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    public function test_user_can_view_own_account(): void
    {
        $user = $this->makeUser(User::ROLE_STAFF);

        $this->actingAs($user)
            ->get(route('account.edit'))
            ->assertOk()
            ->assertSee($user->email);
    }

    public function test_user_can_change_own_email_with_correct_current_password(): void
    {
        $user = $this->makeUser(User::ROLE_STAFF, [
            'password' => Hash::make('correct-password'),
        ]);

        $this->actingAs($user)
            ->put(route('account.email.update'), [
                'email' => 'new-email@example.com',
                'current_password' => 'correct-password',
            ])
            ->assertRedirect(route('account.edit'));

        $this->assertSame('new-email@example.com', $user->fresh()->email);
    }

    public function test_user_cannot_change_email_without_correct_current_password(): void
    {
        $user = $this->makeUser(User::ROLE_STAFF, [
            'password' => Hash::make('correct-password'),
        ]);

        $this->actingAs($user)
            ->put(route('account.email.update'), [
                'email' => 'new-email@example.com',
                'current_password' => 'wrong-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertNotSame('new-email@example.com', $user->fresh()->email);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $existing = $this->makeUser(User::ROLE_STAFF, ['email' => 'taken@example.com']);

        $user = $this->makeUser(User::ROLE_STAFF, [
            'password' => Hash::make('correct-password'),
        ]);

        $this->actingAs($user)
            ->put(route('account.email.update'), [
                'email' => $existing->email,
                'current_password' => 'correct-password',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_user_can_change_own_password_with_correct_current_password(): void
    {
        $user = $this->makeUser(User::ROLE_STAFF, [
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'old-password',
                'password' => 'brand-new-password-123',
                'password_confirmation' => 'brand-new-password-123',
            ])
            ->assertRedirect(route('account.edit'));

        $this->assertTrue(Hash::check('brand-new-password-123', $user->fresh()->password));
    }

    public function test_incorrect_current_password_is_rejected_when_changing_password(): void
    {
        $user = $this->makeUser(User::ROLE_STAFF, [
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'brand-new-password-123',
                'password_confirmation' => 'brand-new-password-123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_password_confirmation_is_required(): void
    {
        $user = $this->makeUser(User::ROLE_STAFF, [
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'old-password',
                'password' => 'brand-new-password-123',
                'password_confirmation' => 'does-not-match',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_new_password_is_hashed(): void
    {
        $user = $this->makeUser(User::ROLE_STAFF, [
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'old-password',
            'password' => 'brand-new-password-123',
            'password_confirmation' => 'brand-new-password-123',
        ]);

        $storedPassword = $user->fresh()->password;

        $this->assertNotSame('brand-new-password-123', $storedPassword);
        $this->assertStringStartsWith('$2y$', $storedPassword);
    }

    public function test_forced_password_change_blocks_normal_use_until_password_is_changed(): void
    {
        $user = $this->makeUser(User::ROLE_STAFF, [
            'password' => Hash::make('temporary-password'),
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('account.edit'));

        // The account page itself, and the password-update endpoint, must
        // remain reachable — otherwise the user could never escape.
        $this->actingAs($user)
            ->get(route('account.edit'))
            ->assertOk();

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'temporary-password',
                'password' => 'a-new-permanent-password',
                'password_confirmation' => 'a-new-permanent-password',
            ])
            ->assertRedirect(route('account.edit'));

        $this->assertFalse($user->fresh()->must_change_password);

        // Now that it's been changed, normal navigation works again.
        $this->actingAs($user->fresh())
            ->get(route('dashboard'))
            ->assertOk();
    }
}
