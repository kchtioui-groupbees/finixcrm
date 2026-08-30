<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use Database\Seeders\PaymentMethodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new PaymentMethodSeeder())->run();
    }

    public function test_guest_gets_200_for_public_payment_methods_page(): void
    {
        $this->get('/payment-methods')->assertOk();
    }

    public function test_guest_gets_200_for_about_page(): void
    {
        $this->get('/about')->assertOk();
    }

    public function test_guest_gets_200_for_terms_page(): void
    {
        $this->get('/terms')->assertOk();
    }

    public function test_public_pages_never_redirect_to_login(): void
    {
        foreach (['/payment-methods', '/about', '/terms'] as $path) {
            $response = $this->get($path);
            $response->assertOk();
            $this->assertFalse($response->isRedirect(), "{$path} must not redirect");
        }
    }

    public function test_terms_page_has_the_expected_title_and_content(): void
    {
        $response = $this->get('/terms');

        $response->assertSee('Conditions d&#039;utilisation', false);
        $response->assertSee('Objet du service');
        $response->assertSee('contact@finix.tn');
    }

    public function test_about_page_has_the_expected_content(): void
    {
        $response = $this->get('/about');

        $response->assertSee('Bienvenue chez Finix', false);
        $response->assertSee('contact@finix.tn');
    }

    public function test_inactive_payment_methods_are_not_shown_publicly(): void
    {
        // usdt_trc20 is seeded inactive (no wallet address configured yet).
        $response = $this->get('/payment-methods');

        $response->assertDontSee('USDT TRC20');
    }

    public function test_private_payment_methods_are_not_shown_publicly(): void
    {
        PaymentMethod::where('key', 'd17')->update(['is_public' => false]);

        $response = $this->get('/payment-methods');

        $response->assertDontSee('D17');
    }

    public function test_incomplete_payment_methods_are_not_shown_publicly(): void
    {
        // virement_bancaire is active+public but its RIB fields are all null.
        $response = $this->get('/payment-methods');

        $response->assertDontSee('Virement Bancaire');
    }

    public function test_public_custom_fields_are_visible(): void
    {
        $response = $this->get('/payment-methods');

        $response->assertSee('92 871 752');
    }

    public function test_private_custom_fields_stay_hidden(): void
    {
        $method = PaymentMethod::where('key', 'wafacash')->first();
        $method->fields()->create([
            'label' => 'Internal note',
            'value' => 'Secret internal detail',
            'type' => 'text',
            'is_public' => false,
            'copyable' => false,
            'sort_order' => 99,
        ]);

        $response = $this->get('/payment-methods');

        $response->assertDontSee('Internal note');
        $response->assertDontSee('Secret internal detail');
    }

    public function test_unknown_fee_message_is_visible(): void
    {
        $response = $this->get('/payment-methods');

        $response->assertSee(PaymentMethod::UNKNOWN_FEE_LABEL);
    }

    public function test_unknown_fee_is_never_shown_as_no_fee(): void
    {
        $flouci = PaymentMethod::where('key', 'flouci')->first();
        $this->assertSame('unknown', $flouci->fee_type);
        $this->assertNull($flouci->fee_value);

        $response = $this->get('/payment-methods');
        $html = $response->getContent();

        // The unknown-fee message must appear; "No fee" must not be
        // associated with flouci anywhere on the page.
        $response->assertSee(PaymentMethod::UNKNOWN_FEE_LABEL);
    }

    public function test_archived_payment_methods_are_never_shown_publicly(): void
    {
        PaymentMethod::where('key', 'kashy')->update(['archived_at' => now()]);

        $response = $this->get('/payment-methods');

        $response->assertDontSee('Kashy');
    }

    public function test_legacy_methods_with_no_category_are_never_shown_publicly(): void
    {
        $mandat = PaymentMethod::where('key', 'mandat')->first();
        $this->assertNull($mandat->category);

        $response = $this->get('/payment-methods');

        $response->assertDontSee('Mandat');
    }

    public function test_private_portal_pages_still_require_authentication(): void
    {
        $response = $this->get('/portal/payment-methods');
        $response->assertRedirect(route('login'));
    }

    public function test_admin_payment_method_management_still_requires_authentication(): void
    {
        $response = $this->get('/payments/methods');
        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_still_requires_authentication(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_internal_notes_and_confirmation_fields_are_never_exposed_publicly(): void
    {
        $method = PaymentMethod::where('key', 'd17')->first();
        $method->update(['fee_label' => null]); // d17 has a known fee, no internal label needed

        $response = $this->get('/payment-methods');

        // requires_confirmation is surfaced as a soft reassurance message,
        // never as raw internal field names or booleans.
        $response->assertDontSee('requires_confirmation');
        $response->assertDontSee('is_public');
        $response->assertDontSee('archived_at');
    }

    public function test_public_footer_links_never_point_to_protected_client_routes(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee(route('client.about'));
        $response->assertDontSee(route('client.payment-methods'));
        $response->assertSee(route('public.about'));
        $response->assertSee(route('public.payment-methods'));
        $response->assertSee(route('public.terms'));
    }
}
