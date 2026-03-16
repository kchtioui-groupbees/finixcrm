<?php

namespace App\Livewire\Payments;

use Livewire\Component;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\PaymentProof;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentForm extends Component
{
    use \Livewire\WithFileUploads;

    public $paymentId = null;
    public $client_id = '';
    public $order_id = '';
    public $amount = '';
    public $payment_method = 'virement_bancaire';
    public $status = 'completed';
    public $payment_date = '';
    public $type = 'specific_order'; // specific_order, balance
    public $internal_notes = '';
    public $credit_balance = 0; // Current client credit balance
    public $currency = 'USD';
    
    public $new_proofs = [];
    public $existing_proofs = [];

    // For UI
    public $unpaid_orders = [];

    public function mount(?Payment $payment = null, $order_id = null)
    {
        if ($payment && $payment->exists) {
            $this->paymentId = $payment->id;
            $this->client_id = $payment->client_id;
            $this->order_id = $payment->order_id;
            $this->amount = $payment->amount;
            $this->payment_method = $payment->payment_method;
            $this->status = $payment->status;
            $this->payment_date = $payment->payment_date ? Carbon::parse($payment->payment_date)->format('Y-m-d') : null;
            $this->existing_proofs = $payment->proofs;
            $this->type = $payment->type ?: 'specific_order';
            $this->internal_notes = $payment->internal_notes;
        } else {
            $this->payment_date = now()->format('Y-m-d');
            $this->client_id = request()->query('client_id', '');

            if ($order_id) {
                $order = Order::find($order_id);
                if ($order) {
                    $this->order_id = $order_id;
                    $this->client_id = $order->client_id;
                    $this->amount = $order->pending_amount;
                }
            }
        }

        if ($this->client_id) {
            $this->loadUnpaidOrders();
            $this->loadClientBalance();
        }
    }

    public function updatedClientId($value)
    {
        $this->order_id = '';
        if ($value) {
            $this->loadUnpaidOrders();
            $this->loadClientBalance();
        } else {
            $this->unpaid_orders = [];
            $this->credit_balance = 0;
        }
    }

    protected function loadClientBalance()
    {
        $client = Client::find($this->client_id);
        $this->credit_balance = $client ? $client->credit_balance : 0;
        $this->currency = $client ? $client->currency : 'USD';
    }

    public function updatedOrderId($value)
    {
        if ($value && $this->type === 'specific_order') {
            $order = Order::find($value);
            if ($order) {
                $this->amount = $order->pending_amount;
            }
        }
    }

    public function updatedType($value)
    {
        if ($value === 'balance') {
            $this->order_id = '';
        }
    }

    protected function loadUnpaidOrders()
    {
        // Get all orders that are not fully paid
        $this->unpaid_orders = Order::with('product')
            ->where('client_id', $this->client_id)
            ->get()
            ->filter(function($order) {
                return $order->payment_status !== 'paid';
            })->values()->all();
    }

    protected function rules()
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'order_id' => 'required_if:type,specific_order|nullable|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:255',
            'status' => 'required|string|in:completed,pending,failed',
            'payment_date' => 'required|date',
            'type' => 'required|in:specific_order,balance',
            'internal_notes' => 'nullable|string',
            'new_proofs.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    public function deleteProof($proofId)
    {
        $proof = PaymentProof::findOrFail($proofId);
        Storage::disk('public')->delete($proof->file_path);
        $proof->delete();
        
        $this->existing_proofs = PaymentProof::where('payment_id', $this->paymentId)->get();
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function() {
            $payment = Payment::updateOrCreate(
                ['id' => $this->paymentId],
                [
                    'client_id'      => $this->client_id,
                    'order_id'       => $this->type === 'specific_order' ? $this->order_id : null,
                    'amount'         => $this->amount,
                    'payment_method' => $this->payment_method,
                    'status'         => $this->status,
                    'payment_date'   => $this->payment_date,
                    'type'           => $this->type,
                    'internal_notes' => $this->internal_notes,
                    'currency'       => $this->currency,
                ]
            );

            // Handle File Uploads
            if (!empty($this->new_proofs)) {
                foreach ($this->new_proofs as $file) {
                    $path = $file->store('payment_proofs', 'public');

                    PaymentProof::create([
                        'payment_id'    => $payment->id,
                        'file_path'     => $path,
                        'file_type'     => $file->getMimeType(),
                        'original_name' => $file->getClientOriginalName(),
                    ]);
                }
            }
        });

        // Run global reallocation outside the payment-save transaction
        // so that all allocations reflect the full state of completed payments.
        $allocationService = app(\App\Services\PaymentAllocationService::class);
        $allocationService->reallocateForClient((int) $this->client_id);

        session()->flash('message', $this->paymentId ? 'Payment updated successfully.' : 'Payment logged successfully.');

        return redirect()->route('payments.index');
    }

    public function formatAmount($amount)
    {
        $decimals = ($this->currency === 'TND') ? 3 : 2;
        $symbol = match($this->currency) {
            'USD' => '$',
            'EUR' => '€',
            'TND' => 'TND ',
            default => $this->currency . ' '
        };

        return $symbol . number_format($amount, $decimals);
    }

    public function render()
    {
        $clients = Client::orderBy('name')->get();

        return view('livewire.payments.payment-form', [
            'clients' => $clients
        ])->layout('layouts.app');
    }
}
