<?php

namespace App\Livewire\Payments;

use App\Models\PaymentMethod;
use App\Models\PaymentMethodField;
use Livewire\Component;
use Livewire\WithFileUploads;

class PaymentMethodForm extends Component
{
    use WithFileUploads;

    public $paymentMethodId = null;
    public $key = '';

    // Informations générales
    public $label = '';
    public $category = 'other';
    public $description = '';
    public $logo = null; // new upload, if any
    public $existingLogoPath = null;
    public $instructions = '';

    // Coordonnées de paiement — repeatable custom fields
    public $customFields = [];

    // Devises
    public $currencies = [];

    // Frais
    public $fee_type = 'unknown';
    public $fee_value = null;
    public $fee_currency = 'TND';
    public $fee_paid_by = 'customer';

    // Vérification
    public $requires_confirmation = true;
    public $proof_required = false;
    public $reference_required = false;

    // Affichage public
    public $is_active = true;
    public $is_public = true;
    public $sort_order = 0;

    public function mount(?PaymentMethod $paymentMethod = null)
    {
        if ($paymentMethod && $paymentMethod->exists) {
            $this->paymentMethodId = $paymentMethod->id;
            $this->key = $paymentMethod->key;
            $this->label = $paymentMethod->label;
            $this->category = $paymentMethod->category ?? 'other';
            $this->description = $paymentMethod->description;
            $this->existingLogoPath = $paymentMethod->logo_path;
            $this->instructions = $paymentMethod->instructions;
            $this->currencies = $paymentMethod->currencies ?? [];
            $this->fee_type = $paymentMethod->fee_type ?? 'unknown';
            $this->fee_value = $paymentMethod->fee_value;
            $this->fee_currency = $paymentMethod->fee_currency ?? ($this->currencies[0] ?? 'TND');
            $this->fee_paid_by = $paymentMethod->fee_paid_by ?? 'customer';
            $this->requires_confirmation = $paymentMethod->requires_confirmation;
            $this->proof_required = $paymentMethod->proof_required;
            $this->reference_required = $paymentMethod->reference_required;
            $this->is_active = $paymentMethod->is_active;
            $this->is_public = $paymentMethod->is_public;
            $this->sort_order = $paymentMethod->sort_order;

            $this->customFields = $paymentMethod->fields->map(fn ($f) => [
                'id' => $f->id,
                'label' => $f->label,
                'value' => $f->value,
                'type' => $f->type,
                'is_public' => $f->is_public,
                'copyable' => $f->copyable,
            ])->values()->all();
        } else {
            $this->sort_order = (PaymentMethod::max('sort_order') ?? 0) + 10;
        }
    }

    public function addCustomField()
    {
        $this->customFields[] = [
            'id' => null,
            'label' => '',
            'value' => '',
            'type' => 'text',
            'is_public' => true,
            'copyable' => false,
        ];
    }

    public function removeCustomField(int $index)
    {
        unset($this->customFields[$index]);
        $this->customFields = array_values($this->customFields);
    }

    protected function rules()
    {
        return [
            'label' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(PaymentMethod::CATEGORIES)),
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'instructions' => 'nullable|string',
            'currencies' => 'required|array|min:1',
            'currencies.*' => 'in:TND,EUR,USD',
            'fee_type' => 'required|string|in:' . implode(',', PaymentMethod::FEE_TYPES),
            'fee_value' => 'nullable|numeric|min:0|required_if:fee_type,fixed,percentage',
            'fee_currency' => 'nullable|string|max:3',
            'fee_paid_by' => 'required|string|in:' . implode(',', PaymentMethod::FEE_PAID_BY),
            'requires_confirmation' => 'boolean',
            'proof_required' => 'boolean',
            'reference_required' => 'boolean',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'sort_order' => 'integer|min:0',
            'customFields' => 'array',
            'customFields.*.label' => 'nullable|string|max:255',
            'customFields.*.value' => 'nullable|string|max:2000',
            'customFields.*.type' => 'required|string|in:' . implode(',', PaymentMethodField::TYPES),
            'customFields.*.is_public' => 'boolean',
            'customFields.*.copyable' => 'boolean',
        ];
    }

    public function getFeeLabelPreviewProperty(): string
    {
        return $this->fee_type === 'unknown'
            ? PaymentMethod::UNKNOWN_FEE_LABEL
            : '';
    }

    public function save()
    {
        $this->validate();

        $isNew = is_null($this->paymentMethodId);

        $logoPath = $this->existingLogoPath;
        if ($this->logo) {
            $logoPath = $this->logo->store('payment_method_logos', 'public');
        }

        $method = PaymentMethod::updateOrCreate(
            ['id' => $this->paymentMethodId],
            array_merge([
                'label' => $this->label,
                'category' => $this->category,
                'description' => $this->description ?: null,
                'logo_path' => $logoPath,
                'instructions' => $this->instructions ?: null,
                'currencies' => array_values($this->currencies),
                'fee_type' => $this->fee_type,
                'fee_value' => $this->fee_type === 'none' ? 0 : ($this->fee_type === 'unknown' ? null : $this->fee_value),
                'fee_currency' => $this->fee_currency ?: null,
                'fee_paid_by' => $this->fee_type === 'none' ? 'none' : $this->fee_paid_by,
                'fee_label' => $this->fee_type === 'unknown' ? PaymentMethod::UNKNOWN_FEE_LABEL : null,
                'requires_confirmation' => $this->requires_confirmation,
                'proof_required' => $this->proof_required,
                'reference_required' => $this->reference_required,
                'is_active' => $this->is_active,
                'is_public' => $this->is_public,
                'sort_order' => $this->sort_order,
            ], $isNew ? ['key' => PaymentMethod::generateUniqueKey($this->label)] : [])
        );

        $keptFieldIds = [];
        foreach ($this->customFields as $i => $field) {
            if (trim((string) $field['label']) === '') {
                continue;
            }

            $saved = $method->fields()->updateOrCreate(
                ['id' => $field['id'] ?? null],
                [
                    'label' => $field['label'],
                    'value' => $field['value'] !== '' ? $field['value'] : null,
                    'type' => $field['type'],
                    'is_public' => (bool) $field['is_public'],
                    'copyable' => (bool) $field['copyable'],
                    'sort_order' => $i,
                ]
            );
            $keptFieldIds[] = $saved->id;
        }
        $method->fields()->whereNotIn('id', $keptFieldIds)->delete();

        session()->flash('message', $isNew ? __('Payment method created.') : __('Payment method updated.'));

        return redirect()->route('payments.methods');
    }

    public function render()
    {
        return view('livewire.payments.payment-method-form', [
            'categories' => PaymentMethod::CATEGORIES,
        ])->layout('layouts.app');
    }
}
