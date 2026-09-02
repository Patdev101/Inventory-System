<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_guest_is_redirected_to_login_for_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirectToRoute('login');
    }

    public function test_login_page_is_available_to_guests(): void
    {
        $this->get('/login')->assertOk();
    }
}
