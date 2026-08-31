<?php

namespace Tests\Feature;

use App\Livewire\Clients\ClientIndex;
use App\Models\Client;
use App\Models\ClientBalanceTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression coverage for the pre-commit audit finding that
 * client_balance_transactions.client_id was onDelete('cascade'), so
 * deleting a client would silently and permanently destroy their refund
 * and cashback history. The FK is now onDelete('restrict').
 */
class ClientDeleteProtectsBalanceHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_OWNER]);
    }

    public function test_a_client_with_balance_history_cannot_be_deleted(): void
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 40]);
        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 40, 'type' => 'refund',
            'description' => 'Avoir client', 'currency' => 'TND',
        ]);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientIndex::class)
            ->call('deleteClient', $client->id);

        $this->assertDatabaseHas('clients', ['id' => $client->id]);
        $this->assertDatabaseHas('client_balance_transactions', ['client_id' => $client->id, 'type' => 'refund']);
    }

    public function test_a_client_with_no_balance_history_can_still_be_deleted(): void
    {
        $client = Client::create(['name' => 'Sara', 'currency' => 'TND', 'credit_balance' => 0]);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientIndex::class)
            ->call('deleteClient', $client->id);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
