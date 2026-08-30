<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $paymentMethodId ? __('Edit Payment Method') : __('New Payment Method') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="mb-6 flex bg-gray-100 dark:bg-gray-900 p-1 rounded-lg w-fit">
                        <a href="{{ route('payments.index') }}" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500">{{ __('All Payments') }}</a>
                        <a href="{{ route('payments.pending') }}" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500">{{ __('Pending Confirmation') }}</a>
                        <a href="{{ route('payments.methods') }}" class="px-4 py-1.5 text-xs font-bold rounded-md bg-white dark:bg-gray-700 shadow-sm">{{ __('Payment Methods') }}</a>
                    </div>

                    <form wire:submit="save" class="space-y-10">

                        <!-- Informations générales -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('General Information') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="label" :value="__('Name')" />
                                    <x-text-input id="label" type="text" class="mt-1 block w-full" wire:model="label" required />
                                    <x-input-error :messages="$errors->get('label')" class="mt-2" />
                                    @if($key)
                                        <p class="text-[10px] text-gray-400 mt-1">{{ __('Key') }}: <span class="font-mono">{{ $key }}</span></p>
                                    @else
                                        <p class="text-[10px] text-gray-400 mt-1">{{ __('A unique key will be generated automatically from the name.') }}</p>
                                    @endif
                                </div>
                                <div>
                                    <x-input-label for="category" :value="__('Category')" />
                                    <select id="category" wire:model="category" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                        @foreach($categories as $value => $categoryLabel)
                                            <option value="{{ $value }}">{{ $categoryLabel }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('category')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="description" :value="__('Description')" />
                                    <textarea id="description" wire:model="description" rows="2" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm"></textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="logo" :value="__('Logo / Icon')" />
                                    <input type="file" id="logo" wire:model="logo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <div wire:loading wire:target="logo" class="text-xs text-blue-500 mt-1 italic">{{ __('Uploading...') }}</div>
                                    @if($logo)
                                        <img src="{{ $logo->temporaryUrl() }}" class="mt-2 h-12 w-12 object-cover rounded-lg border border-gray-200">
                                    @elseif($existingLogoPath)
                                        <img src="{{ Storage::url($existingLogoPath) }}" class="mt-2 h-12 w-12 object-cover rounded-lg border border-gray-200">
                                    @endif
                                    <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="instructions" :value="__('Payment instructions')" />
                                    <textarea id="instructions" wire:model="instructions" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm"></textarea>
                                    <x-input-error :messages="$errors->get('instructions')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        <!-- Coordonnées de paiement -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Payment Details') }}</h3>
                                <button type="button" wire:click="addCustomField" class="text-xs font-bold text-indigo-600 hover:underline">{{ __('+ Add field') }}</button>
                            </div>
                            <p class="text-xs text-gray-400">{{ __('Add as many custom fields as needed — phone number, RIB, IBAN, wallet address, email, anything. Nothing here is invented for you.') }}</p>

                            <div class="space-y-3">
                                @foreach($customFields as $i => $field)
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start p-4 bg-gray-50 dark:bg-gray-900/40 rounded-lg border border-gray-100 dark:border-gray-800" wire:key="field-{{ $i }}">
                                        <div class="md:col-span-3">
                                            <x-input-label :value="__('Field label')" class="text-[10px]" />
                                            <x-text-input type="text" class="mt-1 block w-full text-sm" wire:model="customFields.{{ $i }}.label" placeholder="{{ __('e.g. RIB') }}" />
                                            <x-input-error :messages="$errors->get('customFields.'.$i.'.label')" class="mt-1" />
                                        </div>
                                        <div class="md:col-span-3">
                                            <x-input-label :value="__('Value')" class="text-[10px]" />
                                            <x-text-input type="text" class="mt-1 block w-full text-sm font-mono" wire:model="customFields.{{ $i }}.value" placeholder="{{ __('leave empty until known') }}" />
                                        </div>
                                        <div class="md:col-span-2">
                                            <x-input-label :value="__('Type')" class="text-[10px]" />
                                            <select wire:model="customFields.{{ $i }}.type" class="mt-1 block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                                                <option value="text">{{ __('Text') }}</option>
                                                <option value="phone">{{ __('Phone') }}</option>
                                                <option value="email">{{ __('Email') }}</option>
                                                <option value="link">{{ __('Link') }}</option>
                                                <option value="wallet_address">{{ __('Wallet address') }}</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2 flex items-end gap-4 h-full pb-2">
                                            <label class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                                <input type="checkbox" wire:model="customFields.{{ $i }}.is_public" class="rounded border-gray-300 text-indigo-600">
                                                {{ __('Public') }}
                                            </label>
                                            <label class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                                <input type="checkbox" wire:model="customFields.{{ $i }}.copyable" class="rounded border-gray-300 text-indigo-600">
                                                {{ __('Copy button') }}
                                            </label>
                                        </div>
                                        <div class="md:col-span-2 flex items-end justify-end h-full pb-1">
                                            <button type="button" wire:click="removeCustomField({{ $i }})" class="text-xs font-bold text-red-500 hover:underline">{{ __('Remove') }}</button>
                                        </div>
                                    </div>
                                @endforeach
                                @if(count($customFields) === 0)
                                    <p class="text-sm text-gray-400 italic p-4 text-center border border-dashed border-gray-200 rounded-lg">{{ __('No fields yet.') }}</p>
                                @endif
                            </div>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        <!-- Devises -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Currencies') }}</h3>
                            <div class="flex gap-6">
                                @foreach(['TND', 'EUR', 'USD'] as $cur)
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" value="{{ $cur }}" wire:model="currencies" class="rounded border-gray-300 text-indigo-600">
                                        <span class="font-bold text-sm">{{ $cur }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('currencies')" class="mt-2" />
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        <!-- Frais -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Payment Fees') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <x-input-label for="fee_type" :value="__('Fee type')" />
                                    <select id="fee_type" wire:model.live="fee_type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                                        <option value="none">{{ __('No fee') }}</option>
                                        <option value="fixed">{{ __('Fixed amount') }}</option>
                                        <option value="percentage">{{ __('Percentage') }}</option>
                                        <option value="unknown">{{ __('Unknown fee') }}</option>
                                    </select>
                                </div>
                                @if(in_array($fee_type, ['fixed', 'percentage']))
                                    <div>
                                        <x-input-label for="fee_value" :value="__('Value')" />
                                        <x-text-input id="fee_value" type="number" step="0.001" class="mt-1 block w-full" wire:model="fee_value" />
                                        <x-input-error :messages="$errors->get('fee_value')" class="mt-2" />
                                    </div>
                                    @if($fee_type === 'fixed')
                                        <div>
                                            <x-input-label for="fee_currency" :value="__('Fee currency')" />
                                            <select id="fee_currency" wire:model="fee_currency" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                                                @foreach(['TND', 'EUR', 'USD'] as $cur)
                                                    <option value="{{ $cur }}">{{ $cur }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                @endif
                                @if($fee_type !== 'none')
                                    <div>
                                        <x-input-label for="fee_paid_by" :value="__('Fee paid by')" />
                                        <select id="fee_paid_by" wire:model="fee_paid_by" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                                            <option value="customer">{{ __('Customer') }}</option>
                                            <option value="merchant">{{ __('Finix') }}</option>
                                            <option value="none">{{ __('None') }}</option>
                                        </select>
                                    </div>
                                @endif
                            </div>
                            @if($fee_type === 'unknown')
                                <div class="bg-amber-50 border border-amber-100 rounded-lg p-3 text-xs text-amber-700">
                                    {{ $this->feeLabelPreview }}
                                </div>
                            @endif
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        <!-- Vérification -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Verification') }}</h3>
                            <div class="flex flex-wrap gap-8">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" wire:model="requires_confirmation" class="rounded border-gray-300 text-indigo-600">
                                    <span class="text-sm">{{ __('Requires manual confirmation') }}</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" wire:model="proof_required" class="rounded border-gray-300 text-indigo-600">
                                    <span class="text-sm">{{ __('Proof of payment required') }}</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" wire:model="reference_required" class="rounded border-gray-300 text-indigo-600">
                                    <span class="text-sm">{{ __('Reference required') }}</span>
                                </label>
                            </div>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        <!-- Affichage public -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Display') }}</h3>
                            <div class="flex flex-wrap items-end gap-8">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-emerald-600">
                                    <span class="text-sm">{{ __('Active') }}</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" wire:model="is_public" class="rounded border-gray-300 text-emerald-600">
                                    <span class="text-sm">{{ __('Show on public page') }}</span>
                                </label>
                                <div>
                                    <x-input-label for="sort_order" :value="__('Display order')" class="text-[10px]" />
                                    <x-text-input id="sort_order" type="number" class="mt-1 block w-32" wire:model="sort_order" />
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        <!-- Aperçu -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Preview') }}</h3>
                            <div class="max-w-sm bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
                                <div class="flex items-center gap-3 mb-3">
                                    @if($logo)
                                        <img src="{{ $logo->temporaryUrl() }}" class="h-10 w-10 object-cover rounded-xl">
                                    @elseif($existingLogoPath)
                                        <img src="{{ Storage::url($existingLogoPath) }}" class="h-10 w-10 object-cover rounded-xl">
                                    @else
                                        <div class="h-10 w-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500 font-black">{{ mb_substr($label ?: '?', 0, 2) }}</div>
                                    @endif
                                    <div>
                                        <div class="font-black text-gray-900 dark:text-white">{{ $label ?: __('Method name') }}</div>
                                        <div class="text-[10px] text-gray-400 uppercase font-bold">{{ $categories[$category] ?? '' }}</div>
                                    </div>
                                </div>
                                @if($description)
                                    <p class="text-xs text-gray-500 mb-3">{{ $description }}</p>
                                @endif
                                <div class="flex flex-wrap gap-1 mb-3">
                                    @foreach($currencies as $cur)
                                        <span class="text-[10px] font-bold bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">{{ $cur }}</span>
                                    @endforeach
                                </div>
                                @foreach($customFields as $field)
                                    @if(!empty($field['label']) && !empty($field['is_public']))
                                        <div class="text-xs mb-1">
                                            <span class="text-gray-400">{{ $field['label'] }}:</span>
                                            <span class="font-mono font-bold text-gray-700 dark:text-gray-300">{{ $field['value'] ?: __('(not set)') }}</span>
                                        </div>
                                    @endif
                                @endforeach
                                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-xs font-bold text-gray-600 dark:text-gray-400">
                                    {{ __('Fee') }}: {{ $fee_type === 'unknown' ? $this->feeLabelPreview : ($fee_type === 'none' ? __('No fee') : ($fee_value !== null && $fee_value !== '' ? $fee_value . ($fee_type === 'percentage' ? '%' : ' '.$fee_currency) : __('Not configured'))) }}
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('payments.methods') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 text-sm font-medium">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn-phoenix h-11 px-8 text-xs">{{ $paymentMethodId ? __('Save Changes') : __('Create Payment Method') }}</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
