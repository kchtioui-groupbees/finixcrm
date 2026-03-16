<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class ProductShow extends Component
{
    use WithPagination;

    public Product $product;
    
    // Dynamic filters
    public $search = '';
    public $filters = []; // [field_id => value]
    
    public $activeTab = 'orders';

    public function mount(Product $product)
    {
        $this->product = $product->load('fields');
        
        // Initialize filters for searchable fields
        foreach ($this->product->fields as $field) {
            $this->filters[$field->id] = '';
        }
    }

    public function resetFilters()
    {
        $this->reset('filters', 'search');
    }

    public function render()
    {
        $query = Order::with(['client', 'fieldValues.field'])
            ->where('product_id', $this->product->id);

        if (!empty($this->search)) {
            $query->whereHas('client', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Apply Dynamic Custom Field Filters
        foreach ($this->filters as $fieldId => $value) {
            if ($value !== '' && $value !== null) {
                $query->whereHas('fieldValues', function($q) use ($fieldId, $value) {
                    $q->where('product_field_id', $fieldId)
                      ->where('value', 'like', '%' . $value . '%');
                });
            }
        }

        $orders = $query->latest()->paginate(10);

        return view('livewire.products.product-show', [
            'orders' => $orders
        ])->layout('layouts.app');
    }
}
