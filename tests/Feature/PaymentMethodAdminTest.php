<?php

namespace Tests\Feature;

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

    public function test_admin_can_fill_in_bank_transfer_rib(): void
    {
        $method = PaymentMethod::where('key', 'virement_bancaire')->first();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PaymentMethodIndex::class)
            ->call('openEdit', $method->id)
            ->set('editHolder', 'Khaled Chtioui')
            ->set('editBankName', 'BIAT')
            ->set('editRib', '12345678901234567890')
            ->call('saveDetails');

        $method->refresh();

        $this->assertSame('Khaled Chtioui', $method->details['holder']);
        $this->assertSame('BIAT', $method->details['bank_name']);
        $this->assertSame('12345678901234567890', $method->details['rib']);
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

        Livewire::actingAs($admin)
            ->test(PaymentMethodIndex::class)
            ->call('openEdit', $method->id)
            ->set('editWalletAddress', 'TXaBBQZbCS5hjypNjcvdMD8SzKq1sSSKMd')
            ->call('saveDetails')
            ->call('toggleActive', $method->id);

        $method->refresh();
        $this->assertSame('TXaBBQZbCS5hjypNjcvdMD8SzKq1sSSKMd', $method->details['wallet_address']);
        $this->assertTrue($method->is_active);
    }

    public function test_admin_can_toggle_requires_confirmation(): void
    {
        $method = PaymentMethod::where('key', 'especes')->first();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PaymentMethodIndex::class)
            ->call('toggleConfirmation', $method->id);

        $this->assertTrue($method->fresh()->requires_confirmation);
    }
}
