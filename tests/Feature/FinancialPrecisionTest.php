<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientBalanceTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Regression coverage for a real, pre-existing precision bug caught while
 * building the Finix balance auto-apply feature: every money column
 * (orders.price, payments.amount, payment_allocations.amount,
 * client_balance_transactions.amount, clients.credit_balance,
 * products.cashback_value/default_renewal_price) was declared
 * decimal(_, 2), silently truncating or rounding TND's 3rd decimal place
 * (the millime) at the database layer — 0.001 TND was lost entirely.
 *
 * Verified directly against MySQL before fixing (kept here for the
 * record):
 *   DECIMAL(10,2): 45.555 -> 45.56, 0.001 -> 0.00 (!), 0.005 -> 0.01
 *   DECIMAL(12,3): 45.555 -> 45.555, 0.001 -> 0.001, 0.005 -> 0.005
 *
 * See database/migrations/2026_09_02_100000_widen_financial_amounts_to_millime_precision.php.
 */
class FinancialPrecisionTest extends TestCase
{
    use RefreshDatabase;

    private function makeClient(): Client
    {
        return Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);
    }

    private function makeProduct(): Product
    {
        return Product::create(['name' => 'ChatGPT Plus', 'slug' => 'cgpt-' . uniqid(), 'is_active' => true]);
    }

    #[DataProvider('millimeValues')]
    public function test_order_price_preserves_exact_millime_precision(float $value): void
    {
        $client = $this->makeClient();
        $product = $this->makeProduct();

        $order = Order::create([
            'client_id' => $client->id, 'product_id' => $product->id, 'price' => $value,
            'purchase_date' => '2026-08-01', 'expiry_date' => '2027-08-01', 'status' => 'active', 'currency' => 'TND',
        ]);

        $this->assertSame($value, (float) $order->fresh()->price);
    }

    #[DataProvider('millimeValues')]
    public function test_payment_amount_preserves_exact_millime_precision(float $value): void
    {
        $client = $this->makeClient();
        $product = $this->makeProduct();
        $order = Order::create([
            'client_id' => $client->id, 'product_id' => $product->id, 'price' => 1000,
            'purchase_date' => '2026-08-01', 'expiry_date' => '2027-08-01', 'status' => 'active', 'currency' => 'TND',
        ]);

        $payment = Payment::create([
            'client_id' => $client->id, 'order_id' => $order->id, 'amount' => $value,
            'payment_method' => 'especes', 'status' => 'completed', 'payment_date' => '2026-08-02',
            'type' => 'specific_order', 'currency' => 'TND',
        ]);

        $this->assertSame($value, (float) $payment->fresh()->amount);
    }

    #[DataProvider('millimeValues')]
    public function test_client_balance_transaction_amount_preserves_exact_millime_precision(float $value): void
    {
        $client = $this->makeClient();

        $txn = ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => $value, 'type' => 'manual_adjustment',
            'description' => 'Precision test', 'currency' => 'TND',
        ]);

        $this->assertSame($value, (float) $txn->fresh()->amount);
    }

    #[DataProvider('millimeValues')]
    public function test_payment_allocation_amount_preserves_exact_millime_precision(float $value): void
    {
        $client = $this->makeClient();
        $product = $this->makeProduct();
        $order = Order::create([
            'client_id' => $client->id, 'product_id' => $product->id, 'price' => 1000,
            'purchase_date' => '2026-08-01', 'expiry_date' => '2027-08-01', 'status' => 'active', 'currency' => 'TND',
        ]);
        $txn = ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => -$value, 'type' => 'usage',
            'description' => 'Precision test', 'currency' => 'TND',
        ]);

        $allocation = PaymentAllocation::create([
            'balance_transaction_id' => $txn->id, 'order_id' => $order->id, 'amount' => $value,
        ]);

        $this->assertSame($value, (float) $allocation->fresh()->amount);
    }

    #[DataProvider('millimeValues')]
    public function test_client_credit_balance_preserves_exact_millime_precision(float $value): void
    {
        $client = $this->makeClient();

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => $value, 'type' => 'manual_adjustment',
            'description' => 'Precision test', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $this->assertSame($value, (float) $client->fresh()->credit_balance);
    }

    public static function millimeValues(): array
    {
        return [
            '45.530' => [45.530],
            '0.001' => [0.001],
            '0.005' => [0.005],
            '45.555' => [45.555],
        ];
    }

    // ── End-to-end: the exact scenario from the spec ────────────────────

    public function test_end_to_end_auto_apply_with_the_exact_spec_example_values(): void
    {
        $client = $this->makeClient();
        $product = $this->makeProduct();
        $order = Order::create([
            'client_id' => $client->id, 'product_id' => $product->id, 'price' => 45.000,
            'purchase_date' => '2026-08-01', 'expiry_date' => '2027-08-01', 'status' => 'active', 'currency' => 'TND',
        ]);

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 45.530, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        app(\App\Services\FinixBalanceAutoApplyService::class)->applyToUnpaidOrders($client->fresh());

        $order->refresh();
        $this->assertSame(45.000, (float) $order->paid_amount);
        $this->assertSame(0.000, (float) $order->pending_amount);
        $this->assertSame('completed', $order->status);
        // Exactly 0.530 left over — not 0.53 rounded away, not 0 truncated.
        $this->assertSame(0.530, (float) $client->fresh()->credit_balance);
    }

    public function test_a_lone_millime_credit_is_never_lost(): void
    {
        $client = $this->makeClient();
        $product = $this->makeProduct();
        $order = Order::create([
            'client_id' => $client->id, 'product_id' => $product->id, 'price' => 0.001,
            'purchase_date' => '2026-08-01', 'expiry_date' => '2027-08-01', 'status' => 'active', 'currency' => 'TND',
        ]);

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 0.001, 'type' => 'cashback_reward',
            'description' => 'A single millime', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $this->assertSame(0.001, (float) $client->fresh()->credit_balance);

        app(\App\Services\FinixBalanceAutoApplyService::class)->applyToUnpaidOrders($client->fresh());

        $order->refresh();
        $this->assertSame(0.001, (float) $order->paid_amount);
        $this->assertSame('completed', $order->status);
    }
}
