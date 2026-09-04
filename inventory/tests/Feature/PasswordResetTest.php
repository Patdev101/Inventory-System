<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_available_to_guests(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    public function test_forgot_password_page_instructs_the_user_to_contact_an_admin(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
        $response->assertSee('contact an administrator', false);
        $response->assertDontSee('<form', false);
    }

    public function test_forgot_password_never_sends_a_reset_email(): void
    {
        Notification::fake();

        User::factory()->create(['email' => 'someone@example.com']);

        $this->get(route('password.request'))->assertOk();

        Notification::assertNothingSent();
    }

    public function test_email_based_password_reset_routes_no_longer_exist(): void
    {
        // GET /forgot-password still exists (it's the contact-an-admin
        // page) but there is no POST handler to send a reset email.
        $this->post('/forgot-password', ['email' => 'someone@example.com'])
            ->assertStatus(405);

        $this->get('/reset-password/some-token')
            ->assertStatus(404);
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first('email')
        );
    }
}
