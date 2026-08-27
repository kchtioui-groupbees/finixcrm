<?php

namespace Tests\Feature;

use App\Livewire\DueDates\RenewModal;
use App\Livewire\Payments\PendingPaymentIndex;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\PaymentConfirmationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class PaymentConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);
        $product = Product::create([
            'name' => 'ChatGPT Plus',
            'slug' => 'chatgpt-plus-' . uniqid(),
            'is_active' => true,
            'renewable' => true,
            'renewal_interval_unit' => 'month',
            'renewal_interval_value' => 1,
            'default_renewal_price' => 30,
        ]);

        return Order::create(array_merge([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'price' => 30,
            'purchase_date' => '2026-08-26',
            'expiry_date' => '2027-08-26',
            'status' => 'active',
            'currency' => 'TND',
            'renewable' => true,
            'renewal_interval_unit' => 'month',
            'renewal_interval_value' => 1,
            'renewal_price' => 30,
            'next_due_date' => '2026-08-26',
        ], $overrides));
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_OWNER]);
    }

    public function test_bank_transfer_payment_is_created_pending(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(RenewModal::class)
            ->call('openFor', $order->id)
            ->set('payment_method', 'virement_bancaire')
            ->set('amount', 30)
            ->set('reference', 'WF839201')
            ->set('payment_date', '2026-08-26')
            ->set('internal_notes', 'Virement BIAT annoncé à 14h20')
            ->call('confirmRenewal');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'pending',
            'payment_method' => 'virement_bancaire',
            'reference' => 'WF839201',
        ]);
    }

    public function test_pending_payment_does_not_affect_client_credit_or_order_paid_amount(): void
    {
        $order = $this->makeOrder();
        $client = $order->client;
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(RenewModal::class)
            ->call('openFor', $order->id)
            ->set('payment_method', 'virement_bancaire')
            ->set('amount', 30)
            ->set('payment_date', '2026-08-26')
            ->call('confirmRenewal');

        $client->refresh();
        $order->refresh();

        $this->assertSame(0.0, (float) $client->credit_balance);
        $this->assertSame(0.0, (float) $order->paid_amount);
    }

    public function test_pending_payment_does_not_renew_the_subscription(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(RenewModal::class)
            ->call('openFor', $order->id)
            ->set('payment_method', 'virement_bancaire')
            ->set('amount', 30)
            ->set('payment_date', '2026-08-26')
            ->call('confirmRenewal');

        $order->refresh();

        $this->assertSame('2026-08-26', $order->next_due_date->toDateString());
    }

    public function test_confirming_a_renewal_payment_advances_next_due_date(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        $payment = Payment::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'amount' => 30,
            'payment_method' => 'virement_bancaire',
            'status' => 'pending',
            'payment_date' => '2026-08-26',
            'type' => 'renewal',
            'currency' => 'TND',
        ]);

        app(PaymentConfirmationService::class)->confirm($payment, $admin);

        $order->refresh();
        $payment->refresh();

        $this->assertSame('completed', $payment->status);
        $this->assertNotNull($payment->confirmed_at);
        $this->assertSame($admin->id, $payment->confirmed_by);
        $this->assertSame('2026-09-26', $order->next_due_date->toDateString());
    }

    public function test_next_due_date_advances_only_once_even_if_confirm_is_attempted_twice(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        $payment = Payment::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'amount' => 30,
            'payment_method' => 'virement_bancaire',
            'status' => 'pending',
            'payment_date' => '2026-08-26',
            'type' => 'renewal',
            'currency' => 'TND',
        ]);

        app(PaymentConfirmationService::class)->confirm($payment, $admin);

        $this->expectException(RuntimeException::class);
        app(PaymentConfirmationService::class)->confirm($payment->fresh(), $admin);
    }

    public function test_double_confirmation_is_impossible_and_renewal_runs_once(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        $payment = Payment::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'amount' => 30,
            'payment_method' => 'virement_bancaire',
            'status' => 'pending',
            'payment_date' => '2026-08-26',
            'type' => 'renewal',
            'currency' => 'TND',
        ]);

        app(PaymentConfirmationService::class)->confirm($payment, $admin);

        try {
            app(PaymentConfirmationService::class)->confirm($payment->fresh(), $admin);
        } catch (RuntimeException $e) {
            // expected
        }

        $order->refresh();
        // A second (rejected) confirmation attempt must not advance the date again.
        $this->assertSame('2026-09-26', $order->next_due_date->toDateString());
    }

    public function test_rejecting_a_payment_does_not_renew_the_subscription(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        $payment = Payment::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'amount' => 30,
            'payment_method' => 'virement_bancaire',
            'status' => 'pending',
            'payment_date' => '2026-08-26',
            'type' => 'renewal',
            'currency' => 'TND',
        ]);

        app(PaymentConfirmationService::class)->reject($payment, $admin, 'Reçu introuvable');

        $order->refresh();
        $payment->refresh();

        $this->assertSame('rejected', $payment->status);
        $this->assertNotNull($payment->rejected_at);
        $this->assertSame($admin->id, $payment->rejected_by);
        $this->assertSame('2026-08-26', $order->next_due_date->toDateString());
    }

    public function test_double_rejection_is_impossible(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        $payment = Payment::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'amount' => 30,
            'payment_method' => 'virement_bancaire',
            'status' => 'pending',
            'payment_date' => '2026-08-26',
            'type' => 'renewal',
            'currency' => 'TND',
        ]);

        app(PaymentConfirmationService::class)->reject($payment, $admin);

        $this->expectException(RuntimeException::class);
        app(PaymentConfirmationService::class)->reject($payment->fresh(), $admin);
    }

    public function test_note_and_reference_are_preserved_through_confirmation(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        $payment = Payment::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'amount' => 30,
            'payment_method' => 'virement_bancaire',
            'reference' => 'WF839201',
            'internal_notes' => 'Mandat Wafacash #WF839201',
            'status' => 'pending',
            'payment_date' => '2026-08-26',
            'type' => 'renewal',
            'currency' => 'TND',
        ]);

        app(PaymentConfirmationService::class)->confirm($payment, $admin);
        $payment->refresh();

        $this->assertSame('WF839201', $payment->reference);
        $this->assertSame('Mandat Wafacash #WF839201', $payment->internal_notes);
    }

    public function test_payment_method_without_confirmation_requirement_follows_normal_workflow(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(RenewModal::class)
            ->call('openFor', $order->id)
            ->set('payment_method', 'especes')
            ->set('amount', 30)
            ->set('payment_date', '2026-08-26')
            ->call('confirmRenewal');

        $order->refresh();

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'especes',
            'status' => 'completed',
        ]);
        $this->assertSame('2026-09-26', $order->next_due_date->toDateString());
    }

    public function test_pending_payments_page_filters_by_method_and_client(): void
    {
        $orderA = $this->makeOrder();
        $orderB = $this->makeOrder();
        $admin = $this->admin();

        $paymentA = Payment::create([
            'client_id' => $orderA->client_id,
            'order_id' => $orderA->id,
            'amount' => 30,
            'payment_method' => 'virement_bancaire',
            'status' => 'pending',
            'payment_date' => '2026-08-26',
            'type' => 'renewal',
            'currency' => 'TND',
        ]);

        $paymentB = Payment::create([
            'client_id' => $orderB->client_id,
            'order_id' => $orderB->id,
            'amount' => 15,
            'payment_method' => 'wafacash',
            'status' => 'pending',
            'payment_date' => '2026-08-26',
            'type' => 'renewal',
            'currency' => 'TND',
        ]);

        Livewire::actingAs($admin)
            ->test(PendingPaymentIndex::class)
            ->set('payment_method', 'wafacash')
            ->assertViewHas('payments', function ($payments) use ($paymentA, $paymentB) {
                $ids = $payments->pluck('id');
                return $ids->contains($paymentB->id) && !$ids->contains($paymentA->id);
            });

        Livewire::actingAs($admin)
            ->test(PendingPaymentIndex::class)
            ->set('client_id', $orderA->client_id)
            ->assertViewHas('payments', function ($payments) use ($paymentA, $paymentB) {
                $ids = $payments->pluck('id');
                return $ids->contains($paymentA->id) && !$ids->contains($paymentB->id);
            });
    }

    public function test_double_confirmation_never_creates_a_second_allocation(): void
    {
        $order = $this->makeOrder(['renewable' => false, 'renewal_interval_unit' => null, 'renewal_interval_value' => null, 'renewal_price' => null, 'next_due_date' => null]);
        $admin = $this->admin();

        $payment = Payment::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'amount' => 30,
            'payment_method' => 'virement_bancaire',
            'status' => 'pending',
            'payment_date' => '2026-08-26',
            'type' => 'specific_order',
            'currency' => 'TND',
        ]);

        app(PaymentConfirmationService::class)->confirm($payment, $admin);
        $allocationsAfterFirstConfirm = \App\Models\PaymentAllocation::where('payment_id', $payment->id)->count();

        try {
            app(PaymentConfirmationService::class)->confirm($payment->fresh(), $admin);
        } catch (RuntimeException $e) {
            // expected
        }

        $allocationsAfterSecondAttempt = \App\Models\PaymentAllocation::where('payment_id', $payment->id)->count();

        $this->assertSame(1, $allocationsAfterFirstConfirm);
        $this->assertSame($allocationsAfterFirstConfirm, $allocationsAfterSecondAttempt);
    }
}
