<?php

namespace Tests\Feature;

use App\Livewire\Payments\PaymentMethodForm;
use App\Livewire\Payments\PaymentMethodIndex;
use App\Models\PaymentMethod;
use App\Models\User;
use Database\Seeders\PaymentMethodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentMethodAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new PaymentMethodSeeder())->run();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_OWNER]);
    }

    public function test_admin_can_create_a_new_payment_method_with_custom_fields(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PaymentMethodForm::class)
            ->set('label', 'Postepay')
            ->set('category', 'card')
            ->set('description', 'Carte prépayée italienne')
            ->set('currencies', ['EUR'])
            ->set('fee_type', 'percentage')
            ->set('fee_value', 2.5)
            ->set('fee_paid_by', 'customer')
            ->call('addCustomField')
            ->set('customFields.0.label', 'Numéro de carte')
            ->set('customFields.0.value', '')
            ->set('customFields.0.type', 'text')
            ->set('customFields.0.is_public', true)
            ->set('customFields.0.copyable', true)
            ->call('save')
            ->assertRedirect(route('payments.methods'));

        $method = PaymentMethod::where('label', 'Postepay')->first();
        $this->assertNotNull($method);
        $this->assertSame('postepay', $method->key);
        $this->assertSame('card', $method->category);
        $this->assertSame(['EUR'], $method->currencies);
        $this->assertSame('percentage', $method->fee_type);
        $this->assertEquals(2.5, $method->fee_value);
        $this->assertSame(1, $method->fields()->count());
        $this->assertSame('Numéro de carte', $method->fields()->first()->label);
    }

    public function test_admin_can_fully_edit_an_existing_method_like_d17(): void
    {
        $method = PaymentMethod::where('key', 'd17')->first();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PaymentMethodForm::class, ['paymentMethod' => $method])
            ->assertSet('label', 'D17')
            ->set('label', 'D17 Wallet')
            ->set('description', 'Paiement via D17')
            ->set('fee_value', 1.5)
            ->call('save')
            ->assertRedirect(route('payments.methods'));

        $method->refresh();
        $this->assertSame('D17 Wallet', $method->label);
        $this->assertSame('Paiement via D17', $method->description);
        $this->assertEquals(1.5, $method->fee_value);
        // Key never changes on edit.
        $this->assertSame('d17', $method->key);
        // Pre-existing fields survive the edit.
        $this->assertSame(2, $method->fields()->count());
    }

    public function test_edit_form_can_add_and_remove_custom_fields(): void
    {
        $method = PaymentMethod::where('key', 'wafacash')->first();
        $originalFieldCount = $method->fields()->count();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PaymentMethodForm::class, ['paymentMethod' => $method])
            ->call('addCustomField')
            ->set("customFields.{$originalFieldCount}.label", 'Email')
            ->set("customFields.{$originalFieldCount}.value", 'contact@wafacash.tn')
            ->set("customFields.{$originalFieldCount}.type", 'email')
            ->call('removeCustomField', 0)
            ->call('save');

        $method->refresh();
        $this->assertSame($originalFieldCount, $method->fields()->count());
        $this->assertNotNull($method->fields()->where('label', 'Email')->first());
    }

    public function test_form_validation_requires_label_category_and_currency(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PaymentMethodForm::class)
            ->set('label', '')
            ->set('currencies', [])
            ->call('save')
            ->assertHasErrors(['label', 'currencies']);
    }

    public function test_fee_value_required_when_fee_type_is_fixed_or_percentage(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PaymentMethodForm::class)
            ->set('label', 'Test Method')
            ->set('currencies', ['TND'])
            ->set('fee_type', 'fixed')
            ->set('fee_value', null)
            ->call('save')
            ->assertHasErrors(['fee_value']);
    }

    public function test_unknown_fee_never_persists_a_zero_value(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PaymentMethodForm::class)
            ->set('label', 'Test Unknown Fee Method')
            ->set('currencies', ['TND'])
            ->set('fee_type', 'unknown')
            ->call('save');

        $method = PaymentMethod::where('label', 'Test Unknown Fee Method')->first();
        $this->assertSame('unknown', $method->fee_type);
        $this->assertNull($method->fee_value);
        $this->assertSame(PaymentMethod::UNKNOWN_FEE_LABEL, $method->fee_label);
    }

    public function test_none_fee_type_stores_a_real_zero_not_a_placeholder(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PaymentMethodForm::class)
            ->set('label', 'Test No Fee Method')
            ->set('currencies', ['TND'])
            ->set('fee_type', 'none')
            ->call('save');

        $method = PaymentMethod::where('label', 'Test No Fee Method')->first();
        $this->assertSame('none', $method->fee_type);
        $this->assertEquals(0, $method->fee_value);
        $this->assertSame('none', $method->fee_paid_by);
    }

    public function test_crypto_method_cannot_be_activated_without_a_wallet_address(): void
    {
        $method = PaymentMethod::where('key', 'usdt_trc20')->first();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PaymentMethodIndex::class)
            ->call('toggleActive', $method->id);

        $this->assertFalse($method->fresh()->is_active);
    }

    public function test_crypto_method_can_be_activated_once_wallet_address_is_set(): void
    {
        $method = PaymentMethod::where('key', 'usdt_trc20')->first();
        $admin = $this->admin();

        $method->fields()->where('label', 'Adresse wallet')->update(['value' => 'TXaBBQZbCS5hjypNjcvdMD8SzKq1sSSKMd']);

        Livewire::actingAs($admin)
            ->test(PaymentMethodIndex::class)
            ->call('toggleActive', $method->id);

        $this->assertTrue($method->fresh()->is_active);
    }

    public function test_admin_can_duplicate_a_method_with_its_fields(): void
    {
        $method = PaymentMethod::where('key', 'wafacash')->first();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PaymentMethodIndex::class)
            ->call('duplicate', $method->id);

        $clone = PaymentMethod::where('key', 'wafacash_copy')->first();
        $this->assertNotNull($clone);
        $this->assertFalse($clone->is_active);
        $this->assertSame($method->fields()->count(), $clone->fields()->count());
        // Original untouched.
        $this->assertTrue($method->fresh()->is_active);
    }

    public function test_admin_can_archive_and_unarchive_a_method(): void
    {
        $method = PaymentMethod::where('key', 'd17')->first();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PaymentMethodIndex::class)
            ->call('archive', $method->id);

        $method->refresh();
        $this->assertNotNull($method->archived_at);
        $this->assertFalse($method->is_active);

        Livewire::actingAs($admin)
            ->test(PaymentMethodIndex::class)
            ->call('unarchive', $method->id);

        $this->assertNull($method->fresh()->archived_at);
    }

    public function test_archived_methods_are_hidden_from_the_default_list(): void
    {
        $method = PaymentMethod::where('key', 'd17')->first();
        $method->update(['archived_at' => now()]);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PaymentMethodIndex::class)
            ->assertViewHas('methods', function ($methods) use ($method) {
                return !$methods->pluck('id')->contains($method->id);
            });

        Livewire::actingAs($admin)
            ->test(PaymentMethodIndex::class)
            ->set('showArchived', true)
            ->assertViewHas('methods', function ($methods) use ($method) {
                return $methods->pluck('id')->contains($method->id);
            });
    }
}
