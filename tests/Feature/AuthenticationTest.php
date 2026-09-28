<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirectToRoute('login');
    }

    public function test_login_page_is_available_to_guests(): void
    {
        User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->get('/login')->assertOk();
    }

    public function test_login_redirects_to_setup_when_no_admin_exists(): void
    {
        $this->get('/login')->assertRedirectToRoute('setup.create');
        $this->get('/setup')->assertOk();
    }

    public function test_setup_creates_the_first_admin_once(): void
    {
        $this->post('/setup', [
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirectToRoute('login');

        $this->assertDatabaseHas('users', ['email' => 'owner@example.com', 'role' => User::ROLE_ADMIN]);

        $this->get('/setup')->assertNotFound();
        $this->post('/setup', [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'intruder@example.com']);
        $this->get('/login')->assertOk();
    }

    public function test_admin_can_create_another_admin(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->post('/users', [
            'name' => 'Second Admin',
            'email' => 'admin2@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->assertDatabaseHas('users', ['email' => 'admin2@example.com', 'role' => User::ROLE_ADMIN]);
    }
}
