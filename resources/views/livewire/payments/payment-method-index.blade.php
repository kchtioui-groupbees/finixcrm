<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Payment Methods') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="mb-6 flex bg-gray-100 dark:bg-gray-900 p-1 rounded-lg w-fit">
                        <a href="{{ route('payments.index') }}" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500">{{ __('All Payments') }}</a>
                        <a href="{{ route('payments.pending') }}" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500">{{ __('Pending Confirmation') }}</a>
                        <a href="{{ route('payments.methods') }}" class="px-4 py-1.5 text-xs font-bold rounded-md bg-white dark:bg-gray-700 shadow-sm">{{ __('Payment Methods') }}</a>
                    </div>

                    <p class="text-sm text-gray-500 mb-6">{{ __('Toggle whether a method requires manual confirmation before it has any financial effect. This is configuration, not code — changes apply immediately to new payments.') }}</p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">{{ __('Method') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Category') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Fee') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Requires Confirmation') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Active') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Details') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($methods as $method)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700" wire:key="method-{{ $method->id }}">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $method->label }}</div>
                                            <div class="text-xs text-gray-400">{{ $method->key }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-xs">{{ $method->category ? __(ucfirst(str_replace('_', ' ', $method->category))) : '—' }}</td>
                                        <td class="px-6 py-4 text-xs max-w-[220px]">{{ $method->fee_summary }}</td>
                                        <td class="px-6 py-4">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:click="toggleConfirmation({{ $method->id }})" @checked($method->requires_confirmation) class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                            </label>
                                        </td>
                                        <td class="px-6 py-4">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:click="toggleActive({{ $method->id }})" @checked($method->is_active) class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                            </label>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if(in_array($method->category, ['bank_transfer', 'postal_transfer', 'crypto']))
                                                <button type="button" wire:click="openEdit({{ $method->id }})" class="text-xs font-bold text-blue-600 hover:underline">{{ __('Edit details') }}</button>
                                            @else
                                                <span class="text-xs text-gray-300">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">{{ __('Add a payment method') }}</h3>
                        <form wire:submit="addMethod" class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                            <div class="flex-1 w-full">
                                <x-input-label for="newLabel" :value="__('Label')" />
                                <x-text-input id="newLabel" type="text" class="mt-1 block w-full" wire:model="newLabel" placeholder="{{ __('e.g. Postepay') }}" />
                                <x-input-error :messages="$errors->get('newLabel')" class="mt-2" />
                            </div>
                            <label class="inline-flex items-center gap-2 h-11">
                                <input type="checkbox" wire:model="newRequiresConfirmation" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Requires confirmation') }}</span>
                            </label>
                            <button type="submit" class="btn-phoenix h-11 px-6 text-xs">{{ __('Add') }}</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @php $editingMethod = $editingId ? $methods->firstWhere('id', $editingId) : null; @endphp
    @if($editingMethod)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-gray-900/60" wire:click="closeEdit"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 mb-1">{{ __('Edit details') }} — {{ $editingMethod->label }}</h3>
                    <p class="text-sm text-gray-500 mb-6">{{ __('These fields are never guessed — fill them in yourself.') }}</p>

                    <div class="space-y-4">
                        @if($editingMethod->category === 'bank_transfer')
                            <div>
                                <x-input-label for="editHolder" :value="__('Account holder')" />
                                <x-text-input id="editHolder" type="text" class="mt-1 block w-full" wire:model="editHolder" />
                            </div>
                            <div>
                                <x-input-label for="editBankName" :value="__('Bank name')" />
                                <x-text-input id="editBankName" type="text" class="mt-1 block w-full" wire:model="editBankName" />
                            </div>
                            <div>
                                <x-input-label for="editRib" :value="__('RIB')" />
                                <x-text-input id="editRib" type="text" class="mt-1 block w-full font-mono" wire:model="editRib" />
                            </div>
                        @elseif($editingMethod->category === 'postal_transfer')
                            <div>
                                <x-input-label for="editHolder" :value="__('Account holder')" />
                                <x-text-input id="editHolder" type="text" class="mt-1 block w-full" wire:model="editHolder" />
                            </div>
                            <div>
                                <x-input-label for="editRibPostal" :value="__('RIB postal')" />
                                <x-text-input id="editRibPostal" type="text" class="mt-1 block w-full font-mono" wire:model="editRibPostal" />
                            </div>
                        @elseif($editingMethod->category === 'crypto')
                            <div>
                                <x-input-label for="editWalletAddress" :value="__('Wallet address')" />
                                <x-text-input id="editWalletAddress" type="text" class="mt-1 block w-full font-mono" wire:model="editWalletAddress" placeholder="{{ __('e.g. T... (TRC20) or 0x... (BEP20)') }}" />
                                <p class="text-[10px] text-amber-600 mt-1 font-bold">{{ __('This method stays inactive until a wallet address is set here.') }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button wire:click="closeEdit" class="text-sm font-bold text-gray-500">{{ __('Cancel') }}</button>
                        <button wire:click="saveDetails" class="btn-phoenix px-6 h-10 text-xs">{{ __('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
