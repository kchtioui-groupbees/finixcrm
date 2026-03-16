<?php

namespace App\Livewire\Products;

use Livewire\Component;

use Livewire\WithPagination;

class ProductIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $products = \App\Models\Product::withCount('fields')->orderBy('name')->paginate(10);

        return view('livewire.products.product-index', [
            'products' => $products
        ])->layout('layouts.app');
    }
}
