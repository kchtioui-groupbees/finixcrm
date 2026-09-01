<?php

namespace App\Livewire\Products;

use Livewire\Component;

class ProductForm extends Component
{
    public $productId = null;
    public $name = '';
    public $slug = '';
    public $description = '';
    public $category = '';
    public $is_active = true;
    
    // Warranty Defaults
    public $warranty_enabled = false;
    public $warranty_duration_days = 365;
    public $warranty_type = 'Full';
    public $warranty_terms = '';

    // Cashback Defaults
    public $cashback_enabled = false;
    public $cashback_type = 'percentage';
    public $cashback_value = 0.00;

    // Renewal Defaults
    public $renewable = false;
    public $renewal_interval_unit = 'month'; // day, month, year
    public $renewal_interval_value = 1;
    public $default_renewal_price = '';

    public function mount(?\App\Models\Product $product = null)
    {
        if ($product && $product->exists) {
            $this->productId = $product->id;
            $this->name = $product->name;
            $this->slug = $product->slug;
            $this->description = $product->description;
            $this->category = $product->category;
            $this->is_active = $product->is_active;

            $this->warranty_enabled = $product->warranty_enabled;
            $this->warranty_duration_days = $product->warranty_duration_days ?? 365;
            $this->warranty_type = $product->warranty_type ?? 'Full';
            $this->warranty_terms = $product->warranty_terms;

            $this->cashback_enabled = $product->cashback_enabled;
            $this->cashback_type = $product->cashback_type ?? 'percentage';
            $this->cashback_value = $product->cashback_value ?? 0.00;

            $this->renewable = (bool) $product->renewable;
            $this->renewal_interval_unit = $product->renewal_interval_unit ?? 'month';
            $this->renewal_interval_value = $product->renewal_interval_value ?? 1;
            $this->default_renewal_price = $product->default_renewal_price;
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $this->productId,
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'warranty_enabled' => 'boolean',
            'warranty_duration_days' => 'required_if:warranty_enabled,true|nullable|integer|min:0',
            'warranty_type' => 'nullable|string|max:255',
            'warranty_terms' => 'nullable|string',
            'cashback_enabled' => 'boolean',
            'cashback_type' => 'required_if:cashback_enabled,true|in:percentage,fixed',
            // A percentage cannot exceed 100; a fixed reward is a money
            // amount and is capped per-order by the order price instead.
            'cashback_value' => $this->cashback_type === 'percentage'
                ? 'required_if:cashback_enabled,true|nullable|numeric|min:0|max:100'
                : 'required_if:cashback_enabled,true|nullable|numeric|min:0',
            'renewable' => 'boolean',
            'renewal_interval_unit' => 'required_if:renewable,true|nullable|string|in:day,month,year',
            'renewal_interval_value' => 'required_if:renewable,true|nullable|integer|min:1',
            'default_renewal_price' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * lang/{locale}/validation.php is still Laravel's stock English, so the
     * framework defaults would surface in English even under the French
     * locale. These go through __() instead, and are worded for the field
     * rather than reusing the generic ":attribute must not be greater than".
     */
    protected function messages(): array
    {
        $isPercentage = $this->cashback_type === 'percentage';

        return [
            'cashback_value.required_if' => __('A cashback value is required when cashback is enabled.'),
            'cashback_value.numeric' => $isPercentage
                ? __('The cashback percentage must be a number.')
                : __('The cashback amount must be a number.'),
            'cashback_value.min' => $isPercentage
                ? __('The cashback percentage cannot be negative.')
                : __('The cashback amount cannot be negative.'),
            'cashback_value.max' => __('The cashback percentage cannot exceed 100.'),
        ];
    }

    public function updatedName($value)
    {
        if (empty($this->productId) && !empty($value)) {
            $this->slug = \Illuminate\Support\Str::slug($value);
        }
    }

    public function save()
    {
        $this->validate();

        // Captured before the write so the admin can be told the change is
        // forward-only. Orders freeze the rate onto themselves at purchase
        // time (cashback_*_snapshot), so nothing already sold is recomputed.
        $rateChanged = $this->productId
            && \App\Models\Product::whereKey($this->productId)
                ->where(function ($q) {
                    $q->where('cashback_value', '!=', (float) $this->cashback_value)
                      ->orWhere('cashback_type', '!=', $this->cashback_type);
                })
                ->exists();

        $product = \App\Models\Product::updateOrCreate(
            ['id' => $this->productId],
            [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'category' => $this->category,
                'is_active' => $this->is_active,
                'warranty_enabled' => $this->warranty_enabled,
                'warranty_duration_days' => $this->warranty_enabled ? $this->warranty_duration_days : null,
                'warranty_type' => $this->warranty_type,
                'warranty_terms' => $this->warranty_terms,
                'cashback_enabled' => $this->cashback_enabled,
                'cashback_type' => $this->cashback_enabled ? $this->cashback_type : 'percentage',
                // Cast: the column is decimal:3, and an emptied optional box
                // arrives as '' which the cast cannot handle.
                'cashback_value' => $this->cashback_enabled ? (float) $this->cashback_value : 0,
                'renewable' => $this->renewable,
                'renewal_interval_unit' => $this->renewable ? $this->renewal_interval_unit : null,
                'renewal_interval_value' => $this->renewable ? $this->renewal_interval_value : null,
                'default_renewal_price' => $this->renewable ? ($this->default_renewal_price ?: null) : null,
            ]
        );

        if ($rateChanged) {
            session()->flash('message', __('Saved. This percentage will apply to new orders only.'));
        } else {
            session()->flash('message', $this->productId ? __('Product updated successfully.') : __('Product created successfully.'));
        }

        // If it's a new product, redirect to fields manager, else index
        if (!$this->productId) {
            return redirect()->route('products.fields', $product->id);
        }

        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.products.product-form')->layout('layouts.app');
    }
}
