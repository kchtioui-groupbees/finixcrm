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

    // Renewal fields
    public $renewable = false;
    public $renewal_interval_unit = 'month'; // day, month, year
    public $renewal_interval_value = 1;
    public $renewal_price = '';
    public $next_due_date = '';

    // Dynamic fields holder
    public $dynamicFields = [];
    public $dynamicFieldValues = [];

    // Cashback (admin-editable at order creation; the note/expiry stay
    // editable afterwards too, but the amount/type/enabled only up until
    // the reward has actually been paid out, to avoid changing history)
    public $cashback_enabled = false;
    public $cashback_type = 'fixed'; // fixed, percentage
    public $cashback_value = 0;
    public $cashback_note = '';
    public $cashback_expires_at = '';
    public $cashbackManuallyEdited = false;
    public $cashback_already_rewarded = false;

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

            $this->renewable = (bool) $order->renewable;
            $this->renewal_interval_unit = $order->renewal_interval_unit ?? 'month';
            $this->renewal_interval_value = $order->renewal_interval_value ?? 1;
            $this->renewal_price = $order->renewal_price;
            $this->next_due_date = $order->next_due_date ? \Carbon\Carbon::parse($order->next_due_date)->format('Y-m-d') : null;

            $this->cashback_enabled = (bool) $order->cashback_enabled_snapshot;
            $this->cashback_type = $order->cashback_type_snapshot ?? 'fixed';
            $this->cashback_value = $order->cashback_value_snapshot ?? 0;
            $this->cashback_note = $order->cashback_note;
            $this->cashback_expires_at = $order->cashback_expires_at ? \Carbon\Carbon::parse($order->cashback_expires_at)->format('Y-m-d') : null;
            $this->cashbackManuallyEdited = true; // never auto-overwrite an existing snapshot
            $this->cashback_already_rewarded = (bool) $order->cashback_rewarded;

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
        $this->calculateNextDueDatePreview();
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

    public function updatedCashbackEnabled()
    {
        $this->cashbackManuallyEdited = true;
    }

    public function updatedCashbackType()
    {
        $this->cashbackManuallyEdited = true;
    }

    public function updatedCashbackValue()
    {
        $this->cashbackManuallyEdited = true;
    }

    public function updatedRenewable()
    {
        $this->calculateNextDueDatePreview();
    }

    public function updatedRenewalIntervalUnit()
    {
        $this->calculateNextDueDatePreview();
    }

    public function updatedRenewalIntervalValue()
    {
        $this->calculateNextDueDatePreview();
    }

    protected function calculateNextDueDatePreview()
    {
        if (!$this->renewable || !$this->renewal_interval_unit || !$this->renewal_interval_value || !$this->purchase_date) {
            $this->next_due_date = null;
            return;
        }

        try {
            $this->next_due_date = app(\App\Services\RenewalService::class)
                ->calculateNextDueDate(
                    \Carbon\Carbon::parse($this->purchase_date),
                    $this->renewal_interval_unit,
                    (int) $this->renewal_interval_value
                )
                ->format('Y-m-d');
        } catch (\Exception $e) {
            // Silently fail if the interval is invalid
        }
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

                    // Renewal defaults
                    $this->renewable = (bool) $product->renewable;
                    $this->renewal_interval_unit = $product->renewal_interval_unit ?? 'month';
                    $this->renewal_interval_value = $product->renewal_interval_value ?? 1;
                    $this->renewal_price = $product->default_renewal_price;
                    $this->calculateNextDueDatePreview();

                    if (!$this->cashbackManuallyEdited) {
                        $this->cashback_enabled = (bool) $product->cashback_enabled;
                        $this->cashback_type = $product->cashback_type ?? 'fixed';
                        $this->cashback_value = $product->cashback_value ?? 0;
                    }
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

            'renewable' => 'boolean',
            'renewal_interval_unit' => 'required_if:renewable,true|nullable|string|in:day,month,year',
            'renewal_interval_value' => 'required_if:renewable,true|nullable|integer|min:1',
            'renewal_price' => 'nullable|numeric|min:0',

            'cashback_enabled' => 'boolean',
            'cashback_type' => 'required_if:cashback_enabled,true|nullable|string|in:fixed,percentage',
            'cashback_value' => 'required_if:cashback_enabled,true|nullable|numeric|min:0',
            'cashback_note' => 'nullable|string|max:1000',
            'cashback_expires_at' => 'nullable|date',
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

        // ── Cashback: admin-editable overrides of the product default,
        // frozen onto the order at creation. Once a reward has actually
        // been paid out, the amount/type/enabled flag are left alone (only
        // the note/expiry stay editable) so a past reward is never
        // silently changed after the fact.
        $cashbackSnapshotData = [];
        if ($isNewOrder || !$this->cashback_already_rewarded) {
            $tempOrder = new \App\Models\Order([
                'price'    => $this->price,
                'currency' => $this->currency,
            ]);
            $tempOrder->cashback_enabled_snapshot = (bool) $this->cashback_enabled;
            $tempOrder->cashback_type_snapshot    = $this->cashback_type;
            $tempOrder->cashback_value_snapshot   = $this->cashback_value;
            $cashbackAmount = app(\App\Services\CashbackCalculationService::class)->computeAmount($tempOrder);

            $cashbackSnapshotData = [
                'cashback_enabled_snapshot' => (bool) $this->cashback_enabled,
                'cashback_type_snapshot'    => $this->cashback_type,
                'cashback_value_snapshot'   => (float) $this->cashback_value,
                'cashback_amount'           => $cashbackAmount,
            ];

            if ($isNewOrder) {
                $cashbackSnapshotData['cashback_rewarded'] = false;
                $cashbackSnapshotData['cashback_rewarded_at'] = null;
                $cashbackSnapshotData['cashback_reversed'] = false;
            }
        }
        $cashbackSnapshotData['cashback_note'] = $this->cashback_note ?: null;
        $cashbackSnapshotData['cashback_expires_at'] = $this->cashback_expires_at ?: null;

        // ── Renewal: only (re)compute next_due_date when this is a brand new
        // order, or when the order didn't have one configured yet (letting
        // older orders be progressively configured without disturbing an
        // already-advancing cycle on orders that do have one).
        $existingOrder = $isNewOrder ? null : \App\Models\Order::find($this->orderId);
        $shouldComputeNextDueDate = $isNewOrder || !$existingOrder?->next_due_date;

        $renewalData = [
            'renewable'              => $this->renewable,
            'renewal_interval_unit'  => $this->renewable ? $this->renewal_interval_unit : null,
            'renewal_interval_value' => $this->renewable ? $this->renewal_interval_value : null,
            'renewal_price'          => $this->renewable ? ($this->renewal_price ?: null) : null,
        ];

        if ($this->renewable && $this->renewal_interval_unit && $this->renewal_interval_value && $shouldComputeNextDueDate) {
            $renewalData['next_due_date'] = app(\App\Services\RenewalService::class)
                ->calculateNextDueDate(
                    \Carbon\Carbon::parse($this->purchase_date),
                    $this->renewal_interval_unit,
                    (int) $this->renewal_interval_value
                )
                ->format('Y-m-d');
        } elseif (!$this->renewable) {
            $renewalData['next_due_date'] = null;
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
            ], $renewalData, $cashbackSnapshotData)
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
        if (!$this->cashback_enabled || !$this->price) {
            return 0;
        }

        $tempOrder = new \App\Models\Order([
            'price' => $this->price,
            'currency' => $this->currency,
        ]);
        $tempOrder->cashback_enabled_snapshot = true;
        $tempOrder->cashback_type_snapshot = $this->cashback_type;
        $tempOrder->cashback_value_snapshot = $this->cashback_value;

        return app(\App\Services\CashbackCalculationService::class)->computeAmount($tempOrder);
    }
}
