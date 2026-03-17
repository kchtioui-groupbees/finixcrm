<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $productId ? __('Edit Product') : __('Create Product') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form wire:submit.prevent="save" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Product Name')" />
                                <x-text-input wire:model.live="name" id="name" class="block mt-1 w-full" type="text" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Slug -->
                            <div>
                                <x-input-label for="slug" :value="__('Slug (URL friendly)')" />
                                <x-text-input wire:model="slug" id="slug" class="block mt-1 w-full bg-gray-50 dark:bg-gray-900" type="text" required />
                                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Category -->
                        <div>
                            <x-input-label for="category" :value="__('Category (Optional)')" />
                            <x-text-input wire:model="category" id="category" class="block mt-1 w-full md:w-1/2" type="text" placeholder="e.g. Hosting, Software" />
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea wire:model="description" id="description" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Is Active -->
                        <div class="block mt-4">
                            <label for="is_active" class="inline-flex items-center">
                                <input wire:model="is_active" id="is_active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="is_active">
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Product is active and available for orders') }}</span>
                            </label>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        <!-- Warranty Defaults -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Warranty Defaults') }}</h3>
                            <div class="block mt-4">
                                <label for="warranty_enabled" class="inline-flex items-center">
                                    <input wire:model.live="warranty_enabled" id="warranty_enabled" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Enable default warranty for this product') }}</span>
                                </label>
                            </div>

                            @if($warranty_enabled)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                                <div>
                                    <x-input-label for="warranty_duration_days" :value="__('Default Warranty duration (Days)')" />
                                    <x-text-input wire:model="warranty_duration_days" id="warranty_duration_days" class="block mt-1 w-full" type="number" />
                                    <x-input-error :messages="$errors->get('warranty_duration_days')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="warranty_type" :value="__('Warranty Type')" />
                                    <select wire:model="warranty_type" id="warranty_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="Full">{{ __('Full Warranty') }}</option>
                                        <option value="Parts Only">{{ __('Parts Only') }}</option>
                                        <option value="Labor Only">{{ __('Labor Only') }}</option>
                                        <option value="Limited">{{ __('Limited Warranty') }}</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('warranty_type')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="warranty_terms" :value="__('Warranty Terms & Conditions')" />
                                    <textarea wire:model="warranty_terms" id="warranty_terms" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="{{ __('Describe coverage details...') }}"></textarea>
                                    <x-input-error :messages="$errors->get('warranty_terms')" class="mt-2" />
                                </div>
                            </div>
                            @endif
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        <!-- Cashback Rewards -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Cashback Rewards') }}</h3>
                            <div class="block mt-4">
                                <label for="cashback_enabled" class="inline-flex items-center">
                                    <input wire:model.live="cashback_enabled" id="cashback_enabled" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Enable cashback reward for this product') }}</span>
                                </label>
                            </div>

                            @if($cashback_enabled)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-emerald-50 dark:bg-emerald-900/10 rounded-lg">
                                <div>
                                    <x-input-label for="cashback_type" :value="__('Reward Type')" />
                                    <select wire:model="cashback_type" id="cashback_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="percentage">{{ __('Percentage (%)') }}</option>
                                        <option value="fixed">{{ __('Fixed Amount') }}</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('cashback_type')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="cashback_value" :value="__('Reward Value')" />
                                    <x-text-input wire:model="cashback_value" id="cashback_value" class="block mt-1 w-full" type="number" step="0.01" />
                                    <x-input-error :messages="$errors->get('cashback_value')" class="mt-2" />
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('products.index') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 me-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Save Product') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
