<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemHealthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that system health page returns 200 and renders the correct Inertia component
     */
    public function test_system_health_page_returns_correct_response()
    {
        $response = $this->get('/system-health');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('SystemHealth')
            ->has('health')
        );
    }
}