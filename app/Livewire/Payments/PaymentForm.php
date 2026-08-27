<?php

namespace App\Livewire\Payments;

use Livewire\Component;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\PaymentMethod;
use App\Models\PaymentProof;
use App\Services\PaymentAllocationService;
use App\Services\PaymentConfirmationService;
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
    public $payment_method = '';
    public $reference = '';
    public $status = 'completed';
    public $payment_date = '';
    public $type = 'specific_order'; // specific_order, balance
    public $internal_notes = '';
    public $credit_balance = 0; // Current client credit balance
    public $currency = 'USD';
    
    protected $listeners = ['client-selected' => 'handleClientSelected'];

    public $new_proofs = [];
    public $existing_proofs = [];

    // For UI
    public $unpaid_orders = [];
    public $confirmed_at = null;
    public $confirmedByName = null;

    public function mount(?Payment $payment = null, $order_id = null)
    {
        if ($payment && $payment->exists) {
            $this->paymentId = $payment->id;
            $this->client_id = $payment->client_id;
            $this->order_id = $payment->order_id;
            $this->amount = $payment->amount;
            $this->payment_method = $payment->payment_method;
            $this->reference = $payment->reference;
            $this->status = $payment->status;
            $this->payment_date = $payment->payment_date ? Carbon::parse($payment->payment_date)->format('Y-m-d') : null;
            $this->existing_proofs = $payment->proofs;
            $this->confirmed_at = $payment->confirmed_at;
            $this->confirmedByName = $payment->confirmedBy?->name;
            $this->type = $payment->type ?: 'specific_order';
            $this->internal_notes = $payment->internal_notes;
        } else {
            $this->payment_date = now()->format('Y-m-d');
            $this->client_id = request()->query('client_id', '');
            $this->payment_method = PaymentMethod::active()->value('key') ?? '';
            $this->status = PaymentMethod::requiresConfirmation($this->payment_method) ? 'pending' : 'completed';

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

    public function handleClientSelected($id)
    {
        $this->client_id = $id;
        $this->order_id = '';
        if ($id) {
            $this->loadUnpaidOrders();
            $this->loadClientBalance();
        } else {
            $this->unpaid_orders = [];
            $this->credit_balance = 0;
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

    public function updatedPaymentMethod($value)
    {
        // Only auto-default the status while creating — never silently
        // change the status of an existing payment just because the method
        // field was edited.
        if (is_null($this->paymentId)) {
            $this->status = PaymentMethod::requiresConfirmation($value) ? 'pending' : 'completed';
        }
    }

    public function getRequiresConfirmationProperty()
    {
        return PaymentMethod::requiresConfirmation($this->payment_method);
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
            'reference' => 'nullable|string|max:255',
            'status' => 'required|string|in:completed,pending,failed,rejected,cancelled,refunded',
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

        $isNew = is_null($this->paymentId);
        $existing = $isNew ? null : Payment::find($this->paymentId);
        $wasPending = $existing && $existing->status === 'pending';
        // Flipping an existing pending payment to "completed" from this form
        // is a manual confirmation — route it through PaymentConfirmationService
        // so it gets the same atomic renewal/allocation handling and the
        // same guard against confirming twice, instead of just overwriting
        // the status column directly.
        $confirmingNow = $wasPending && $this->status === 'completed';

        $statusToSave = $this->status;
        if ($isNew && PaymentMethod::requiresConfirmation($this->payment_method)) {
            // The client's declaration that they paid is not proof the money
            // arrived — a method that requires confirmation always starts pending.
            $statusToSave = 'pending';
        }
        if ($confirmingNow) {
            $statusToSave = 'pending'; // save the edited fields first, confirm below
        }

        $payment = DB::transaction(function () use ($isNew, $statusToSave) {
            $payment = Payment::updateOrCreate(
                ['id' => $this->paymentId],
                array_merge([
                    'client_id'      => $this->client_id,
                    'order_id'       => $this->type === 'specific_order' ? $this->order_id : null,
                    'amount'         => $this->amount,
                    'payment_method' => $this->payment_method,
                    'reference'      => $this->reference ?: null,
                    'status'         => $statusToSave,
                    'payment_date'   => $this->payment_date,
                    'type'           => $this->type,
                    'internal_notes' => $this->internal_notes,
                    'currency'       => $this->currency,
                ], $isNew ? ['created_by' => auth()->id()] : [])
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

            return $payment;
        });

        if ($confirmingNow) {
            app(PaymentConfirmationService::class)->confirm($payment, auth()->user());
        } else {
            // Run global reallocation outside the payment-save transaction
            // so that all allocations reflect the full state of completed payments.
            app(PaymentAllocationService::class)->reallocateForClient((int) $this->client_id);
        }

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
            'clients' => $clients,
            'paymentMethods' => PaymentMethod::active()->get(),
        ])->layout('layouts.app');
    }
}
