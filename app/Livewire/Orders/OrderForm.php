<?php

namespace App\Livewire\Orders;

use Livewire\Component;

class OrderForm extends Component
{
    public $orderId = null;
    public $client_id = '';
    public $product_id = '';
    public $price = '';
    public $purchase_date = '';
    public $expiry_date = '';
    public $duration = '';
    public $status = 'active';
    public $reminder_date = '';
    public $internal_note = '';

    public $client_credit_balance = 0;
    public $applied_credit = 0;
    public $currency = 'USD';

    protected $listeners = ['client-selected' => 'handleClientSelected'];

    // Expiry calculation
    public $expiry_mode = 'manual'; // manual, calculate
    public $expiry_value = '';
    public $expiry_unit = 'months'; // days, months, years

    // Warranty Fields
    public $warranty_enabled = false;
    public $warranty_duration_days = 365;
    public $warranty_start_mode = 'purchase_date'; // purchase_date, activation_date, custom_date
    public $warranty_start_date = '';
    public $warranty_end_date = '';
    public $warranty_terms_snapshot = '';

    // Dynamic fields holder
    public $dynamicFields = [];
    public $dynamicFieldValues = [];

    public function mount(?\App\Models\Order $order = null)
    {
        if ($order && $order->exists) {
            $this->orderId = $order->id;
            $this->client_id = $order->client_id;
            $this->product_id = $order->product_id;
            $this->price = $order->price;
            $this->purchase_date = $order->purchase_date ? \Carbon\Carbon::parse($order->purchase_date)->format('Y-m-d') : null;
            $this->expiry_date = $order->expiry_date ? \Carbon\Carbon::parse($order->expiry_date)->format('Y-m-d') : null;
            $this->duration = $order->duration;
            $this->status = $order->status;
            $this->reminder_date = $order->reminder_date ? \Carbon\Carbon::parse($order->reminder_date)->format('Y-m-d') : null;
            $this->internal_note = $order->internal_note;

            $this->warranty_enabled = $order->warranty_enabled;
            $this->warranty_duration_days = $order->warranty_duration_days;
            $this->warranty_start_mode = $order->warranty_start_mode;
            $this->warranty_start_date = $order->warranty_start_date ? \Carbon\Carbon::parse($order->warranty_start_date)->format('Y-m-d') : null;
            $this->warranty_end_date = $order->warranty_end_date ? \Carbon\Carbon::parse($order->warranty_end_date)->format('Y-m-d') : null;
            $this->warranty_terms_snapshot = $order->warranty_terms_snapshot;

            // Load existing fields and their values
            $this->loadDynamicFields();
            
            // Map saved values
            foreach ($order->fieldValues as $fv) {
                if (isset($this->dynamicFieldValues[$fv->product_field_id])) {
                    $this->dynamicFieldValues[$fv->product_field_id] = $fv->value;
                }
            }

        } else {
            $this->purchase_date = now()->format('Y-m-d');
            $this->client_id = request()->query('client_id', '');
        }

        if ($this->client_id) {
            $this->loadClientCredit();
        }
    }

    public function handleClientSelected($id)
    {
        $this->client_id = $id;
        if ($id) {
            $this->loadClientCredit();
        } else {
            $this->client_credit_balance = 0;
            $this->applied_credit = 0;
        }
    }

    public function updatedClientId($value)
    {
        if ($value) {
            $this->loadClientCredit();
        } else {
            $this->client_credit_balance = 0;
            $this->applied_credit = 0;
        }
    }

    public function updatedPrice()
    {
        $this->calculateAppliedCredit();
    }

    protected function loadClientCredit()
    {
        $client = \App\Models\Client::find($this->client_id);
        if ($client) {
            $this->client_credit_balance = $client->credit_balance;
            $this->currency = $client->currency;
            $this->calculateAppliedCredit();
        }
    }

    protected function calculateAppliedCredit()
    {
        $price = (float)$this->price;
        if ($price > 0 && $this->client_credit_balance > 0) {
            $this->applied_credit = min($this->client_credit_balance, $price);
        } else {
            $this->applied_credit = 0;
        }
    }

    public function updatedPurchaseDate()
    {
        $this->calculateExpiry();
    }

    public function updatedExpiryMode()
    {
        $this->calculateExpiry();
    }

    public function updatedExpiryValue()
    {
        $this->calculateExpiry();
    }

    public function updatedExpiryUnit()
    {
        $this->calculateExpiry();
    }

    public function updatedExpiryDate()
    {
        $this->calculateReminder();
    }

    public function updatedWarrantyEnabled()
    {
        $this->calculateWarranty();
    }

    public function updatedWarrantyDurationDays()
    {
        $this->calculateWarranty();
    }

    public function updatedWarrantyStartMode()
    {
        $this->calculateWarranty();
    }

    public function updatedWarrantyStartDate()
    {
        if ($this->warranty_start_mode === 'custom_date') {
            $this->calculateWarranty();
        }
    }

    protected function calculateReminder()
    {
        if (!$this->expiry_date) {
            return;
        }

        try {
            $this->reminder_date = \Carbon\Carbon::parse($this->expiry_date)->subDay()->format('Y-m-d');
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    protected function calculateExpiry()
    {
        if ($this->expiry_mode === 'manual' || !$this->purchase_date || !$this->expiry_value) {
            return;
        }

        try {
            $date = \Carbon\Carbon::parse($this->purchase_date);
            $value = (int)$this->expiry_value;

            if ($this->expiry_unit === 'days') {
                $date->addDays($value);
            } elseif ($this->expiry_unit === 'months') {
                $date->addMonths($value);
            } elseif ($this->expiry_unit === 'years') {
                $date->addYears($value);
            }

            $this->expiry_date = $date->format('Y-m-d');
            
            // Trigger reminder calculation after expiry calculation
            $this->calculateReminder();

            // Recalculate warranty if it depends on activation/expiry (though usually purchase/activation)
            if ($this->warranty_start_mode !== 'custom_date') {
                $this->calculateWarranty();
            }
        } catch (\Exception $e) {
            // Silently fail if date is invalid
        }
    }

    protected function calculateWarranty()
    {
        if (!$this->warranty_enabled) {
            return;
        }

        try {
            // Determine start date
            $start = null;
            if ($this->warranty_start_mode === 'purchase_date' && $this->purchase_date) {
                $start = \Carbon\Carbon::parse($this->purchase_date);
            } elseif ($this->warranty_start_mode === 'activation_date' && $this->purchase_date) {
                // For now, activation_date = purchase_date in this simple CRM logic, 
                // but can be extended if we add an 'activated_at' field.
                $start = \Carbon\Carbon::parse($this->purchase_date);
            } elseif ($this->warranty_start_mode === 'custom_date' && $this->warranty_start_date) {
                $start = \Carbon\Carbon::parse($this->warranty_start_date);
            }

            if ($start) {
                $this->warranty_start_date = $start->format('Y-m-d');
                $end = (clone $start)->addDays((int)$this->warranty_duration_days);
                $this->warranty_end_date = $end->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    public function updatedProductId($value)
    {
        $this->loadDynamicFields();
    }

    protected function loadDynamicFields()
    {
        $this->dynamicFields = [];
        $this->dynamicFieldValues = [];

        if ($this->product_id) {
            $product = \App\Models\Product::with('fields')->find($this->product_id);
            if ($product) {
                // Warranty defaults
                if (is_null($this->orderId)) {
                    $this->warranty_enabled = $product->warranty_enabled;
                    $this->warranty_duration_days = $product->warranty_duration_days ?? 365;
                    $this->warranty_terms_snapshot = $product->warranty_terms;
                    $this->calculateWarranty();
                }

                foreach ($product->fields as $field) {
                    $this->dynamicFields[] = $field;
                    $this->dynamicFieldValues[$field->id] = $field->default_value;
                }
            }
        }
    }

    protected function rules()
    {
        $rules = [
            'client_id' => 'required|exists:clients,id',
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:purchase_date',
            'duration' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,expiring_soon,expired,cancelled,completed,partially_paid',
            'reminder_date' => 'nullable|date',
            'internal_note' => 'nullable|string',
            'currency' => 'required|string|max:3',
            
            'warranty_enabled' => 'boolean',
            'warranty_duration_days' => 'required_if:warranty_enabled,true|nullable|integer|min:0',
            'warranty_start_mode' => 'required_if:warranty_enabled,true|string|in:purchase_date,activation_date,custom_date',
            'warranty_start_date' => 'required_if:warranty_start_mode,custom_date|nullable|date',
            'warranty_end_date' => 'nullable|date',
            'warranty_terms_snapshot' => 'nullable|string',
        ];

        // Add dynamic rules
        foreach ($this->dynamicFields as $field) {
            $fieldRules = ['nullable'];
            if ($field->is_required) {
                $fieldRules = ['required'];
            }
            
            if ($field->type === 'email') $fieldRules[] = 'email';
            if ($field->type === 'number') $fieldRules[] = 'numeric';
            if ($field->type === 'url') $fieldRules[] = 'url';
            if ($field->type === 'date') $fieldRules[] = 'date';
            
            $rules['dynamicFieldValues.' . $field->id] = implode('|', $fieldRules);
        }

        return $rules;
    }

    public function save()
    {
        $this->validate();

        $isNewOrder = is_null($this->orderId);

        // ── Snapshot cashback from product BEFORE creating the order ──────
        $cashbackSnapshotData = [];
        if ($this->product_id) {
            $product = \App\Models\Product::find($this->product_id);
            if ($product) {
                $tempOrder = new \App\Models\Order([
                    'price'    => $this->price,
                    'currency' => $this->currency,
                ]);
                $tempOrder->cashback_enabled_snapshot = (bool) $product->cashback_enabled;
                $tempOrder->cashback_type_snapshot    = $product->cashback_type;
                $tempOrder->cashback_value_snapshot   = $product->cashback_value;
                $cashbackAmount = app(\App\Services\CashbackCalculationService::class)->computeAmount($tempOrder);

                $cashbackSnapshotData = [
                    'cashback_enabled_snapshot' => (bool) $product->cashback_enabled,
                    'cashback_type_snapshot'    => $product->cashback_type,
                    'cashback_value_snapshot'   => (float) $product->cashback_value,
                    'cashback_amount'           => $cashbackAmount,
                    'cashback_rewarded'         => false,
                    'cashback_rewarded_at'      => null,
                    'cashback_reversed'         => false,
                ];
            }
        }

        $order = \App\Models\Order::updateOrCreate(
            ['id' => $this->orderId],
            array_merge([
                'client_id'             => $this->client_id,
                'product_id'            => $this->product_id,
                'price'                 => $this->price,
                'purchase_date'         => $this->purchase_date,
                'expiry_date'           => $this->expiry_date,
                'duration'              => $this->duration,
                'status'                => $this->status,
                'reminder_date'         => $this->reminder_date ?: null,
                'internal_note'         => $this->internal_note,
                'warranty_enabled'      => $this->warranty_enabled,
                'warranty_duration_days'=> $this->warranty_duration_days,
                'warranty_start_mode'   => $this->warranty_start_mode,
                'warranty_start_date'   => !empty($this->warranty_start_date) ? $this->warranty_start_date : null,
                'warranty_end_date'     => !empty($this->warranty_end_date) ? $this->warranty_end_date : null,
                'warranty_terms_snapshot' => $this->warranty_terms_snapshot,
                'currency'              => $this->currency,
            ], $isNewOrder ? $cashbackSnapshotData : [])
        );

        // Save dynamic field values
        foreach ($this->dynamicFields as $field) {
            $value = $this->dynamicFieldValues[$field->id] ?? null;
            
            \App\Models\OrderFieldValue::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'product_field_id' => $field->id
                ],
                [
                    'value' => $value
                ]
            );
        }

        // Trigger global reallocation so any existing client balance/payments
        // are redistributed across all orders (including this new one).
        if ($isNewOrder) {
            app(\App\Services\PaymentAllocationService::class)
                ->reallocateForClient((int) $this->client_id);
        }

        session()->flash('message', $this->orderId ? 'Order updated successfully.' : 'Order created successfully.');

        return redirect()->route('orders.index');
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
        return view('livewire.orders.order-form', [
            'clients' => \App\Models\Client::orderBy('name')->get(),
            'products' => \App\Models\Product::where('is_active', true)->orderBy('name')->get()
        ])->layout('layouts.app');
    }

    public function getEstimatedCashbackProperty()
    {
        if (!$this->product_id || !$this->price) return 0;
        
        $product = \App\Models\Product::find($this->product_id);
        if (!$product || !$product->cashback_enabled) return 0;

        if ($product->cashback_type === 'percentage') {
            return ($this->price * $product->cashback_value) / 100;
        } elseif ($product->cashback_type === 'fixed') {
            return $product->cashback_value;
        }
        
        return 0;
    }
}
