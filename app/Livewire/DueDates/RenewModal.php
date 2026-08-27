<?php

namespace App\Livewire\DueDates;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\RenewalService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RenewModal extends Component
{
    public $show = false;
    public $orderId = null;

    public $amount = '';
    public $payment_method = '';
    public $reference = '';
    public $payment_date = '';
    public $internal_notes = '';

    protected $listeners = ['open-renew-modal' => 'openFor'];

    public function openFor($orderId)
    {
        $order = Order::with(['client', 'product'])->findOrFail($orderId);

        $this->orderId = $order->id;
        $this->amount = $order->renewal_price;
        $this->payment_date = now()->format('Y-m-d');
        $this->payment_method = PaymentMethod::active()->value('key') ?? '';
        $this->reference = '';
        $this->internal_notes = '';
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->orderId = null;
    }

    protected function rules()
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'internal_notes' => 'nullable|string',
        ];
    }

    public function confirmRenewal()
    {
        $this->validate();

        $order = Order::findOrFail($this->orderId);
        $requiresConfirmation = PaymentMethod::requiresConfirmation($this->payment_method);

        DB::transaction(function () use ($order, $requiresConfirmation) {
            $payment = Payment::create([
                'client_id' => $order->client_id,
                'order_id' => $order->id,
                'amount' => $this->amount,
                'payment_method' => $this->payment_method,
                'reference' => $this->reference ?: null,
                // A payment made through a method that requires manual
                // confirmation has NO financial effect yet: it must not
                // advance next_due_date until someone actually confirms the
                // money arrived (see PaymentConfirmationService).
                'status' => $requiresConfirmation ? 'pending' : 'completed',
                'payment_date' => $this->payment_date,
                'type' => 'renewal',
                'internal_notes' => $this->internal_notes,
                'currency' => $order->currency,
                'created_by' => auth()->id(),
            ]);

            if (!$requiresConfirmation) {
                app(RenewalService::class)->markRenewed($order);
            }
        });

        session()->flash('message', $requiresConfirmation
            ? __('Payment recorded as pending confirmation. The due date will advance once confirmed.')
            : __('Renewal recorded successfully.'));

        $this->dispatch('renewal-recorded');
        $this->close();
    }

    public function stopRenewal()
    {
        $order = Order::findOrFail($this->orderId);
        app(RenewalService::class)->stopRenewal($order);

        session()->flash('message', __('Renewal stopped for this order.'));
        $this->dispatch('renewal-recorded');
        $this->close();
    }

    public function render()
    {
        $order = $this->orderId ? Order::with(['client', 'product'])->find($this->orderId) : null;

        return view('livewire.due-dates.renew-modal', [
            'order' => $order,
            'paymentMethods' => PaymentMethod::active()->get(),
        ]);
    }
}
