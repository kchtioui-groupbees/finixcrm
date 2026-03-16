<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Manage Custom Fields: {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            <!-- Existing Fields Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Configured Fields</h3>
                    </div>

                    @if(count($fields) === 0)
                         <p class="text-gray-500 italic">No custom fields defined. Orders for this product will contain no extra data.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Order</th>
                                        <th scope="col" class="px-6 py-3">Label / Name</th>
                                        <th scope="col" class="px-6 py-3">Type</th>
                                        <th scope="col" class="px-6 py-3">Validation & Visibility</th>
                                        <th scope="col" class="px-6 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fields as $field)
                                        <tr wire:key="field-row-{{ $field->id }}" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <td class="px-6 py-4">{{ $field->sort_order }}</td>
                                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white flex flex-col">
                                                <span>{{ $field->label }}</span>
                                                <span class="text-xs text-gray-400 font-normal">{{ $field->name }}</span>
                                            </th>
                                            <td class="px-6 py-4">
                                                {{ ucfirst($field->type) }}
                                            </td>
                                            <td class="px-6 py-4 space-x-1">
                                                @if($field->is_required) <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded">Required</span> @else <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2 py-0.5 rounded">Optional</span> @endif
                                                @if($field->is_admin_only) <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2 py-0.5 rounded">Admin Only</span> @endif
                                                @if($field->is_client_visible) <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-0.5 rounded">Client Visible</span> @endif
                                                @if(!$field->is_admin_only && !$field->is_client_visible) <span class="bg-gray-200 text-gray-800 text-xs font-semibold px-2 py-0.5 rounded">Hidden</span> @endif
                                            </td>
                                            <td class="px-6 py-4 text-right space-x-2">
                                                <button wire:click="editField({{ $field->id }})" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</button>
                                                <span class="text-gray-300">|</span>
                                                <button wire:click="deleteField({{ $field->id }})" wire:confirm="Are you sure you want to delete this field?" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Add/Edit Field Form -->
            <div wire:key="field-form-{{ $fieldId ?? 'new' }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">{{ $isEditing ? 'Edit Field' : 'Add New Field' }}</h3>
                    
                    <form wire:submit.prevent="saveField" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Label -->
                            <div>
                                <x-input-label for="label" :value="__('Label (displayed to user)')" />
                                <x-text-input wire:model.live="label" id="label" class="block mt-1 w-full" type="text" required />
                                <x-input-error :messages="$errors->get('label')" class="mt-2" />
                            </div>

                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Name (database key)')" />
                                <x-text-input wire:model="name" id="name" class="block mt-1 w-full bg-gray-50 dark:bg-gray-900" type="text" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Type -->
                            <div>
                                <x-input-label for="type" :value="__('Input Type')" />
                                <select wire:model.live="type" id="type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="text">Text (Short)</option>
                                    <option value="email">Email</option>
                                    <option value="number">Number</option>
                                    <option value="textarea">Textarea (Long Text)</option>
                                    <option value="select">Select Dropdown</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="date">Date</option>
                                    <option value="datetime">Date & Time</option>
                                    <option value="url">URL</option>
                                    <option value="password">Password (Masked)</option>
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>
                        </div>

                        @if($type === 'select')
                            <div>
                                <x-input-label for="options_string" :value="__('Select Options (comma separated)')" />
                                <x-text-input wire:model="options_string" id="options_string" class="block mt-1 w-full" type="text" placeholder="e.g. Basic, Pro, Enterprise" />
                                <p class="text-xs text-gray-500 mt-1">Separate each option with a comma.</p>
                                <x-input-error :messages="$errors->get('options_string')" class="mt-2" />
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Placeholder -->
                            <div>
                                <x-input-label for="placeholder" :value="__('Placeholder')" />
                                <x-text-input wire:model="placeholder" id="placeholder" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('placeholder')" class="mt-2" />
                            </div>

                            <!-- Default Value -->
                            <div>
                                <x-input-label for="default_value" :value="__('Default Value')" />
                                <x-text-input wire:model="default_value" id="default_value" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('default_value')" class="mt-2" />
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <x-input-label for="sort_order" :value="__('Sort Order')" />
                                <x-text-input wire:model="sort_order" id="sort_order" class="block mt-1 w-full" type="number" />
                                <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Checkboxes / Flags -->
                        <div class="flex flex-wrap gap-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <label for="is_required" class="inline-flex items-center">
                                <input wire:model="is_required" id="is_required" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600">
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Required Field</span>
                            </label>

                            <label for="is_client_visible" class="inline-flex items-center">
                                <input wire:model.live="is_client_visible" id="is_client_visible" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600">
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Client Visible (Portal)</span>
                            </label>

                            <label for="is_admin_only" class="inline-flex items-center">
                                <input wire:model.live="is_admin_only" id="is_admin_only" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-red-600 shadow-sm focus:ring-red-500 dark:focus:ring-red-600">
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400 font-semibold text-red-600">Admin Only (Override visibility)</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            @if($isEditing)
                                <button type="button" wire:click="resetField" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 me-4">
                                    Cancel Edit
                                </button>
                            @endif
                            <x-primary-button>
                                {{ $isEditing ? __('Update Field') : __('Add Field') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
