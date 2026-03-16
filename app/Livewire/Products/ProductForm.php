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
            'cashback_value' => 'required_if:cashback_enabled,true|numeric|min:0',
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
                'cashback_value' => $this->cashback_enabled ? $this->cashback_value : 0,
            ]
        );

        session()->flash('message', $this->productId ? 'Product updated successfully.' : 'Product created successfully.');

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
