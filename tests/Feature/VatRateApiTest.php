<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VatRateApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_config_endpoint_returns_the_configured_vat_rate(): void
    {
        config([
            'services.pos.api_token' => 'test-token',
            'pricing.vat_rate' => 12,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer test-token')
            ->getJson('/api/config');

        $response->assertOk();
        $response->assertJson(['vat_rate' => 12.0]);
    }

    public function test_config_endpoint_requires_a_valid_token(): void
    {
        $this->getJson('/api/config')
            ->assertUnauthorized();

        $this->withHeader('Authorization', 'Bearer wrong-token')
            ->getJson('/api/config')
            ->assertUnauthorized();
    }
}
