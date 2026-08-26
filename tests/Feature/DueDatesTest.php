<?php

namespace Tests\Feature;

use App\Livewire\DueDates\DueDateIndex;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DueDatesTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(string $clientName, string $productName, ?string $nextDueDate, bool $renewable = true): Order
    {
        $client = Client::create(['name' => $clientName, 'currency' => 'TND']);
        $product = Product::create([
            'name' => $productName,
            'slug' => \Illuminate\Support\Str::slug($productName) . '-' . uniqid(),
            'is_active' => true,
        ]);

        return Order::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'price' => 30,
            'purchase_date' => now()->subMonth()->toDateString(),
            'expiry_date' => now()->addYear()->toDateString(),
            'status' => 'active',
            'currency' => 'TND',
            'renewable' => $renewable,
            'renewal_interval_unit' => 'month',
            'renewal_interval_value' => 1,
            'renewal_price' => 30,
            'next_due_date' => $nextDueDate,
        ]);
    }

    public function test_dashboard_shows_due_date_kpis(): void
    {
        $this->makeOrder('Ahmed', 'ChatGPT Plus', today()->toDateString());
        $this->makeOrder('Sara', 'Netflix', today()->addDays(3)->toDateString());
        $this->makeOrder('Karim', 'Claude Pro', today()->subDays(2)->toDateString());
        $this->makeOrder('Nadia', 'CapCut Pro', today()->addDays(60)->toDateString());

        $admin = User::factory()->create(['role' => User::ROLE_OWNER]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('dueDates', function ($dueDates) {
            return $dueDates['today']['count'] === 1
                && $dueDates['next7']['count'] === 2
                && $dueDates['overdue']['count'] === 1;
        });
    }

    public function test_due_date_index_filters_by_product(): void
    {
        $chatgptOrder = $this->makeOrder('Ahmed', 'ChatGPT Plus', today()->addDay()->toDateString());
        $netflixOrder = $this->makeOrder('Sara', 'Netflix Profil', today()->addDay()->toDateString());

        $admin = User::factory()->create(['role' => User::ROLE_OWNER]);

        Livewire::actingAs($admin)
            ->test(DueDateIndex::class)
            ->set('product_id', $netflixOrder->product_id)
            ->assertViewHas('orders', function ($orders) use ($netflixOrder, $chatgptOrder) {
                $ids = $orders->pluck('id');
                return $ids->contains($netflixOrder->id) && !$ids->contains($chatgptOrder->id);
            });
    }

    public function test_due_date_index_overdue_filter(): void
    {
        $overdue = $this->makeOrder('Karim', 'Claude Pro', today()->subDays(5)->toDateString());
        $upcoming = $this->makeOrder('Nadia', 'CapCut Pro', today()->addDays(10)->toDateString());

        $admin = User::factory()->create(['role' => User::ROLE_OWNER]);

        Livewire::actingAs($admin)
            ->test(DueDateIndex::class)
            ->call('setQuickFilter', 'overdue')
            ->assertViewHas('orders', function ($orders) use ($overdue, $upcoming) {
                $ids = $orders->pluck('id');
                return $ids->contains($overdue->id) && !$ids->contains($upcoming->id);
            });
    }
}
