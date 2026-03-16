<?php

namespace App\Livewire\Products;

use Livewire\Component;

class ProductFields extends Component
{
    public \App\Models\Product $product;
    
    // Form fields
    public $fieldId = null;
    public $label = '';
    public $name = '';
    public $type = 'text';
    public $placeholder = '';
    public $is_required = false;
    public $is_client_visible = true;
    public $is_admin_only = false;
    public $options_string = ''; // comma separated for select
    public $default_value = '';
    public $sort_order = 0;

    public $isEditing = false;

    public function mount(\App\Models\Product $product)
    {
        $this->product = $product;
    }

    protected function rules()
    {
        return [
            'label' => 'required|string|max:255',
            'name' => 'required|string|max:255|alpha_dash',
            'type' => 'required|string|in:text,email,number,textarea,select,checkbox,date,datetime,url,password',
            'placeholder' => 'nullable|string',
            'is_required' => 'boolean',
            'is_client_visible' => 'boolean',
            'is_admin_only' => 'boolean',
            'options_string' => 'nullable|string',
            'default_value' => 'nullable|string',
            'sort_order' => 'integer',
        ];
    }

    public function updatedLabel($value)
    {
        if (empty($this->fieldId) && !empty($value)) {
            $this->name = str_replace('-', '_', \Illuminate\Support\Str::slug($value));
        }
    }

    public function resetField()
    {
        $this->reset(['fieldId', 'label', 'name', 'type', 'placeholder', 'is_required', 'is_client_visible', 'is_admin_only', 'options_string', 'default_value', 'sort_order', 'isEditing']);
    }

    public function editField($id)
    {
        $this->resetValidation();
        $field = $this->product->fields()->findOrFail($id);
        $this->fieldId = $field->id;
        $this->label = $field->label;
        $this->name = $field->name;
        $this->type = $field->type;
        $this->placeholder = $field->placeholder;
        $this->is_required = $field->is_required;
        $this->is_client_visible = $field->is_client_visible;
        $this->is_admin_only = $field->is_admin_only;
        $this->default_value = $field->default_value;
        $this->sort_order = $field->sort_order;
        
        $this->options_string = is_array($field->options_json) ? implode(', ', $field->options_json) : '';
        $this->isEditing = true;
    }

    public function deleteField($id)
    {
        $this->product->fields()->findOrFail($id)->delete();
        session()->flash('message', 'Field deleted.');
    }

    public function saveField()
    {
        $this->validate();

        $options = null;
        if ($this->type === 'select' && !empty($this->options_string)) {
            $options = array_map('trim', explode(',', $this->options_string));
        }

        $this->product->fields()->updateOrCreate(
            ['id' => $this->fieldId],
            [
                'label' => $this->label,
                'name' => $this->name,
                'type' => $this->type,
                'placeholder' => $this->placeholder,
                'is_required' => $this->is_required,
                'is_client_visible' => $this->is_client_visible,
                'is_admin_only' => $this->is_admin_only,
                'options_json' => $options,
                'default_value' => $this->default_value,
                'sort_order' => $this->sort_order,
            ]
        );

        session()->flash('message', $this->fieldId ? 'Field updated successfully.' : 'Field created successfully.');
        $this->resetField();
    }

    public function render()
    {
        $fields = $this->product->fields;
        
        return view('livewire.products.product-fields', [
            'fields' => $fields
        ])->layout('layouts.app');
    }
}
