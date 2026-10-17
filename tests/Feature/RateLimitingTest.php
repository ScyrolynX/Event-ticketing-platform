<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_login_attempts_are_rate_limited(): void
    {
        User::factory()->create(['email' => 'buyer@example.com']);

        $payload = ['email' => 'buyer@example.com', 'password' => 'wrong-password'];

        // The 'auth' limiter allows 5 attempts per minute per IP — the first
        // 5 should fail normally on bad credentials (401), and the 6th
        // should be stopped by the throttle itself (429) before the
        // controller even runs.
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', $payload)->assertStatus(401);
        }

        $this->postJson('/api/v1/login', $payload)->assertStatus(429);
    }

    public function test_ordinary_api_browsing_is_not_rate_limited(): void
    {
        // Well under both the 60/min baseline and the 5/min auth limiter —
        // this just proves the new throttle middleware doesn't accidentally
        // block normal, unauthenticated traffic to the public events list.
        for ($i = 0; $i < 10; $i++) {
            $this->getJson('/api/v1/events')->assertOk();
        }
    }
}
