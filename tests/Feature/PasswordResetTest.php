<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_available_to_guests(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    public function test_forgot_password_page_shows_the_email_form(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('<form', false);
    }

    public function test_forgot_password_sends_a_reset_link_to_a_known_email(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'someone@example.com']);

        $this->post(route('password.email'), ['email' => 'someone@example.com']);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_does_not_reveal_unknown_emails(): void
    {
        Notification::fake();

        $known = $this->post(route('password.email'), ['email' => 'nobody@example.com']);

        Notification::assertNothingSent();
        $known->assertSessionDoesntHaveErrors('email');
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
