<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use Database\Seeders\PaymentMethodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_is_idempotent(): void
    {
        (new PaymentMethodSeeder())->run();
        $countAfterFirst = PaymentMethod::count();
        $keysAfterFirst = PaymentMethod::pluck('key')->sort()->values();

        (new PaymentMethodSeeder())->run();
        $countAfterSecond = PaymentMethod::count();
        $keysAfterSecond = PaymentMethod::pluck('key')->sort()->values();

        $this->assertSame($countAfterFirst, $countAfterSecond);
        $this->assertTrue($keysAfterFirst->diff($keysAfterSecond)->isEmpty());

        // No duplicate keys at all.
        $this->assertSame(
            PaymentMethod::count(),
            PaymentMethod::distinct('key')->count('key')
        );
    }

    public function test_d17_has_one_percent_fee_paid_by_customer(): void
    {
        (new PaymentMethodSeeder())->run();
        $d17 = PaymentMethod::where('key', 'd17')->first();

        $this->assertSame('wallet', $d17->category);
        $this->assertSame(['TND'], $d17->currencies);
        $this->assertSame('percentage', $d17->fee_type);
        $this->assertEquals(1, $d17->fee_value);
        $this->assertSame('customer', $d17->fee_paid_by);
        $this->assertTrue($d17->requires_confirmation);
        $this->assertTrue($d17->is_active);
    }

    public function test_unknown_fees_are_never_stored_as_zero(): void
    {
        (new PaymentMethodSeeder())->run();

        $unknownFeeMethods = ['flouci', 'wafacash', 'izi_zitouna', 'kashy', 'virement_postal', 'usdt_trc20', 'usdt_bep20'];

        foreach ($unknownFeeMethods as $key) {
            $method = PaymentMethod::where('key', $key)->first();
            $this->assertSame('unknown', $method->fee_type, "{$key} should have fee_type=unknown");
            $this->assertNull($method->fee_value, "{$key} fee_value must be null, never 0, when unknown");
            $this->assertSame('customer', $method->fee_paid_by);
            $this->assertSame(
                'Les éventuels frais de paiement sont à la charge du client.',
                $method->fee_label
            );
        }
    }

    public function test_virement_bancaire_has_fixed_fee_and_no_invented_rib(): void
    {
        (new PaymentMethodSeeder())->run();
        $method = PaymentMethod::where('key', 'virement_bancaire')->first();

        $this->assertSame('bank_transfer', $method->category);
        $this->assertSame(['TND', 'EUR', 'USD'], $method->currencies);
        $this->assertSame('fixed', $method->fee_type);
        $this->assertEquals(2, $method->fee_value);
        $this->assertNull($method->details['rib']);
        $this->assertNull($method->details['holder']);
        $this->assertNull($method->details['bank_name']);
    }

    public function test_virement_postal_has_no_invented_rib(): void
    {
        (new PaymentMethodSeeder())->run();
        $method = PaymentMethod::where('key', 'virement_postal')->first();

        $this->assertSame('postal_transfer', $method->category);
        $this->assertNull($method->details['rib_postal']);
        $this->assertNull($method->details['holder']);
    }

    public function test_usdt_methods_are_inactive_with_no_wallet_address_until_configured(): void
    {
        (new PaymentMethodSeeder())->run();

        foreach (['usdt_trc20' => 'TRC20', 'usdt_bep20' => 'BEP20'] as $key => $network) {
            $method = PaymentMethod::where('key', $key)->first();
            $this->assertSame('crypto', $method->category);
            $this->assertSame(['USD'], $method->currencies);
            $this->assertSame('USDT', $method->details['asset']);
            $this->assertSame($network, $method->details['network']);
            $this->assertNull($method->details['wallet_address']);
            $this->assertFalse($method->is_active);
            $this->assertTrue($method->requires_confirmation);
        }
    }

    public function test_optional_methods_are_created_inactive(): void
    {
        (new PaymentMethodSeeder())->run();

        foreach (['carte_bancaire', 'paymee', 'konnect', 'especes'] as $key) {
            $method = PaymentMethod::where('key', $key)->first();
            $this->assertNotNull($method, "{$key} should exist");
            $this->assertFalse($method->is_active, "{$key} should be created inactive");
        }
    }

    public function test_seeder_updates_existing_methods_without_duplicating(): void
    {
        // 'd17' already exists (seeded generically by the create_payment_methods_table
        // migration) before PaymentMethodSeeder ever runs.
        $existing = PaymentMethod::where('key', 'd17')->first();
        $this->assertNotNull($existing);
        $this->assertNull($existing->category);

        (new PaymentMethodSeeder())->run();

        $this->assertSame(1, PaymentMethod::where('key', 'd17')->count());
        $this->assertSame($existing->id, PaymentMethod::where('key', 'd17')->first()->id);
        $this->assertSame('wallet', PaymentMethod::where('key', 'd17')->first()->category);
    }

    public function test_seeder_does_not_touch_unrelated_existing_methods(): void
    {
        (new PaymentMethodSeeder())->run();

        $mandat = PaymentMethod::where('key', 'mandat')->first();
        $this->assertNotNull($mandat);
        $this->assertNull($mandat->category);
        $this->assertTrue($mandat->is_active);
    }
}
