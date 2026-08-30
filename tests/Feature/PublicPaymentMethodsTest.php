<?php

namespace Tests\Feature;

use App\Livewire\ClientPortal\PaymentMethods;
use App\Models\PaymentMethod;
use App\Models\User;
use Database\Seeders\PaymentMethodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicPaymentMethodsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new PaymentMethodSeeder())->run();
    }

    private function client(): User
    {
        return User::factory()->create(['role' => User::ROLE_CLIENT]);
    }

    public function test_active_public_and_configured_methods_are_shown(): void
    {
        $client = $this->client();

        $response = Livewire::actingAs($client)->test(PaymentMethods::class);

        $response->assertSee('D17');
        $response->assertSee('WafaCash');
    }

    public function test_legacy_methods_with_no_category_are_never_shown(): void
    {
        // 'mandat' predates this feature and has category=null — it must
        // never appear publicly until an admin reviews it through the
        // payment-method form and gives it a category, even though it's
        // technically active and is_public defaults to true.
        $mandat = PaymentMethod::where('key', 'mandat')->first();
        $this->assertNull($mandat->category);
        $this->assertTrue($mandat->is_active);
        $this->assertTrue($mandat->is_public);
        $client = $this->client();

        $response = Livewire::actingAs($client)->test(PaymentMethods::class);

        $response->assertDontSee('Mandat');
    }

    public function test_inactive_methods_are_not_shown(): void
    {
        $client = $this->client();
        // usdt_trc20 is seeded inactive (no wallet address configured yet).

        $response = Livewire::actingAs($client)->test(PaymentMethods::class);

        $response->assertDontSee('USDT TRC20');
    }

    public function test_private_methods_are_not_shown_even_if_active(): void
    {
        PaymentMethod::where('key', 'd17')->update(['is_public' => false]);
        $client = $this->client();

        $response = Livewire::actingAs($client)->test(PaymentMethods::class);

        $response->assertDontSee('D17');
    }

    public function test_archived_methods_are_never_shown(): void
    {
        PaymentMethod::where('key', 'wafacash')->update(['archived_at' => now()]);
        $client = $this->client();

        $response = Livewire::actingAs($client)->test(PaymentMethods::class);

        $response->assertDontSee('WafaCash');
    }

    public function test_bank_transfer_with_no_configured_rib_is_not_shown(): void
    {
        // virement_bancaire is active+public but its RIB fields are all null
        // (never invented) — it should not appear as a usable option.
        $client = $this->client();

        $response = Livewire::actingAs($client)->test(PaymentMethods::class);

        $response->assertDontSee('Virement Bancaire');
    }

    public function test_bank_transfer_appears_once_rib_is_configured(): void
    {
        $method = PaymentMethod::where('key', 'virement_bancaire')->first();
        $method->fields()->where('label', 'RIB')->update(['value' => '12345678901234567890']);
        $client = $this->client();

        $response = Livewire::actingAs($client)->test(PaymentMethods::class);

        $response->assertSee('Virement Bancaire');
        $response->assertSee('12345678901234567890');
    }

    public function test_unknown_fee_message_is_shown_for_public_methods(): void
    {
        $client = $this->client();

        $response = Livewire::actingAs($client)->test(PaymentMethods::class);

        $response->assertSee(PaymentMethod::UNKNOWN_FEE_LABEL);
    }

    public function test_admin_changes_are_immediately_reflected_on_the_public_page(): void
    {
        $method = PaymentMethod::where('key', 'kashy')->first();
        $client = $this->client();

        Livewire::actingAs($client)->test(PaymentMethods::class)->assertSee('Kashy');

        $method->update(['is_public' => false]);

        Livewire::actingAs($client)->test(PaymentMethods::class)->assertDontSee('Kashy');
    }

    public function test_non_public_fields_are_never_shown(): void
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
        $client = $this->client();

        $response = Livewire::actingAs($client)->test(PaymentMethods::class);

        $response->assertDontSee('Internal note');
        $response->assertDontSee('Secret internal detail');
    }
}
