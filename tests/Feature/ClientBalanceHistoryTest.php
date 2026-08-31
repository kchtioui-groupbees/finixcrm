<?php

namespace Tests\Feature;

use App\Livewire\Clients\ClientTransactions;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientBalanceHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_OWNER]);
    }

    public function test_manual_balance_adjustment_records_the_administrator_and_updates_the_cached_balance(): void
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientTransactions::class, ['client' => $client])
            ->set('manualAmount', 25)
            ->set('manualDescription', 'Goodwill credit')
            ->call('manualAdjustment');

        $client->refresh();
        $tx = $client->balanceTransactions()->latest()->first();

        $this->assertSame($admin->id, $tx->created_by);
        $this->assertSame($admin->id, $tx->createdBy->id);
        $this->assertSame(25.0, (float) $client->credit_balance);
    }

    public function test_admin_can_issue_a_refund_as_non_cash_finix_balance(): void
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientTransactions::class, ['client' => $client])
            ->set('manualType', 'refund')
            ->set('manualAmount', 40)
            ->set('manualDescription', 'Avoir suite à un problème confirmé')
            ->call('manualAdjustment');

        $client->refresh();
        $tx = $client->balanceTransactions()->where('type', 'refund')->first();

        $this->assertNotNull($tx);
        $this->assertSame(40.0, (float) $tx->amount);
        $this->assertSame($admin->id, $tx->created_by);
        // Refunds land as Finix balance, never as a direct cash payout.
        $this->assertSame(40.0, (float) $client->credit_balance);
    }

    public function test_refund_history_survives_further_balance_activity(): void
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientTransactions::class, ['client' => $client])
            ->set('manualType', 'refund')
            ->set('manualAmount', 40)
            ->set('manualDescription', 'Avoir client')
            ->call('manualAdjustment');

        $refundId = $client->balanceTransactions()->where('type', 'refund')->first()->id;

        // Further, unrelated balance activity happens later...
        Livewire::actingAs($admin)
            ->test(ClientTransactions::class, ['client' => $client])
            ->set('manualType', 'manual_adjustment')
            ->set('manualAmount', -5)
            ->set('manualDescription', 'Unrelated correction')
            ->call('manualAdjustment');

        // ...the original refund record must still exist, unchanged.
        $this->assertDatabaseHas('client_balance_transactions', [
            'id' => $refundId,
            'type' => 'refund',
            'amount' => 40,
        ]);
    }

    public function test_balance_history_is_never_deleted_by_further_adjustments(): void
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientTransactions::class, ['client' => $client])
            ->set('manualAmount', 25)
            ->set('manualDescription', 'First adjustment')
            ->call('manualAdjustment');

        Livewire::actingAs($admin)
            ->test(ClientTransactions::class, ['client' => $client])
            ->set('manualAmount', -10)
            ->set('manualDescription', 'Second adjustment')
            ->call('manualAdjustment');

        $client->refresh();

        $this->assertSame(2, $client->balanceTransactions()->count());
        $this->assertSame(15.0, (float) $client->credit_balance);
        $this->assertTrue($client->balanceTransactions()->where('description', 'First adjustment')->exists());
        $this->assertTrue($client->balanceTransactions()->where('description', 'Second adjustment')->exists());
    }
}
