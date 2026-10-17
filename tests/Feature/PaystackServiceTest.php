<?php

namespace Tests\Feature;

use App\Services\PaystackService;
use Tests\TestCase;

class PaystackServiceTest extends TestCase
{
    /**
     * Regression test for a real crash: config('services.paystack.secret_key')
     * returns null (not '') whenever PAYSTACK_SECRET_KEY is left out of the
     * environment entirely, rather than present-but-blank. Before the fix,
     * assigning that null into the service's non-nullable string property
     * threw a TypeError — which, since OrderController constructor-injects
     * this service, would 500 every single order-related endpoint (buying a
     * ticket, cancelling, checking in) on any install that configures env
     * vars by omission (common on hosting platforms) instead of copying
     * .env.example's blank placeholder lines.
     */
    public function test_constructing_with_no_paystack_key_configured_at_all_does_not_throw(): void
    {
        config(['services.paystack.secret_key' => null]);

        $service = new PaystackService();

        $this->assertSame([], $service->initializeTransaction('buyer@example.com', 100, 'ref-123'));
        $this->assertSame([], $service->verifyTransaction('ref-123'));
    }

    public function test_constructing_with_a_blank_paystack_key_also_skips_the_network_call(): void
    {
        // The more common real case: .env ships the line present but empty.
        config(['services.paystack.secret_key' => '']);

        $service = new PaystackService();

        $this->assertSame([], $service->initializeTransaction('buyer@example.com', 100, 'ref-123'));
    }
}
