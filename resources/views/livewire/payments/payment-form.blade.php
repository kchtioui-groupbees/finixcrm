<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $paymentId ? __('Edit Payment') : __('New Payment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <form wire:submit="save">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Client -->
                            <div class="md:col-span-2">
                                <x-input-label for="client_id" :value="__('Select Client')" class="text-[10px] uppercase font-bold text-slate-500 mb-1" />
                                <div class="flex flex-col md:flex-row gap-4 items-start">
                                    <div class="flex-1 w-full">
                                        @livewire('components.client-search', ['selectedClientId' => $client_id])
                                    </div>

                                    @if($client_id)
                                        <div class="px-5 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 rounded-xl shrink-0 shadow-sm">
                                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-black uppercase tracking-widest block mb-1">{{ __('Available Credit') }}</span>
                                            <span class="text-xl font-black text-emerald-700 dark:text-emerald-300 leading-none">{{ $this->formatAmount($credit_balance) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                            </div>

                            <!-- Payment Type -->
                            <div>
                                <x-input-label for="type" :value="__('Payment Mode')" />
                                <div class="mt-2 flex gap-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="payment_type" wire:model.live="type" value="specific_order" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                        <span class="ml-2 text-sm">{{ __('Specific Order') }}</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="payment_type" wire:model.live="type" value="balance" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                        <span class="ml-2 text-sm">{{ __('Client Balance (Auto-allocate)') }}</span>
                                    </label>
                                </div>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            <!-- Order Link (if specific) -->
                            @if($type === 'specific_order')
                                <div>
                                    <x-input-label for="order_id" :value="__('Select Order')" />
                                    <select id="order_id" wire:model.live="order_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                        <option value="">-- {{ __('Choose an Order') }} --</option>
                                        @foreach($unpaid_orders as $order)
                                            <option value="{{ $order->id }}">{{ $order->product->name ?? __('Unknown') }} - {{ $order->purchase_date->format('d/m/Y') }} ({{ __('Due') }}: {{ $this->formatAmount($order->pending_amount) }})</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('order_id')" class="mt-2" />
                                </div>
                            @else
                                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-md text-sm text-blue-800 dark:text-blue-300">
                                    <p class="font-semibold mb-2">{{ __('Auto-allocation logic:') }}</p>
                                    <ul class="list-disc ml-5">
                                        <li>{{ __('Payment will be applied to the oldest unpaid orders first.') }}</li>
                                        <li>{{ __('Total pending balance for this client:') }} <strong>{{ $this->formatAmount(collect($unpaid_orders)->sum('pending_amount')) }}</strong></li>
                                    </ul>
                                </div>
                            @endif

                            <div class="md:col-span-2 border-t border-gray-200 dark:border-gray-700 py-2"></div>

                            <!-- Amount -->
                            <div>
                                <x-input-label for="amount" :value="__('Total Amount Paid')" />
                                <x-text-input id="amount" type="number" step="0.01" class="mt-1 block w-full text-lg font-bold" wire:model="amount" required />
                                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <x-input-label for="payment_method" :value="__('Payment Method')" />
                                <select id="payment_method" wire:model="payment_method" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="wafacash">{{ __('Wafacash') }}</option>
                                    <option value="izi_zitouna">{{ __('IZI Zitouna') }}</option>
                                    <option value="virement_bancaire">{{ __('Virement Bancaire') }}</option>
                                    <option value="d17">{{ __('D17') }}</option>
                                    <option value="flouci">{{ __('Flouci') }}</option>
                                    <option value="redotpay">{{ __('Redotpay') }}</option>
                                    <option value="binance">{{ __('Binance') }}</option>
                                    <option value="paypal">{{ __('PayPal') }}</option>
                                    <option value="other">{{ __('Other') }}</option>
                                </select>
                                <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                            </div>
                            
                            <!-- Payment Date -->
                            <div>
                                <x-input-label for="payment_date" :value="__('Payment Date')" />
                                <x-text-input id="payment_date" type="date" class="mt-1 block w-full" wire:model="payment_date" required />
                                <x-input-error :messages="$errors->get('payment_date')" class="mt-2" />
                            </div>

                            <!-- Status -->
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" wire:model="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="completed">{{ __('Completed') }}</option>
                                    <option value="pending">{{ __('Pending') }}</option>
                                    <option value="failed">{{ __('Failed') }}</option>
                                </select>
                                <p class="text-[10px] text-gray-500 mt-1">{{ __('Allocations are only applied when status is "Completed".') }}</p>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>

                            <!-- Internal Notes -->
                            <div class="md:col-span-2">
                                <x-input-label for="internal_notes" :value="__('Internal Notes (Admin Only)')" />
                                <textarea id="internal_notes" wire:model="internal_notes" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 rounded-md shadow-sm" rows="2"></textarea>
                                <x-input-error :messages="$errors->get('internal_notes')" class="mt-2" />
                            </div>

                            <!-- Payment Proofs Upload -->
                            <div class="md:col-span-2 mt-4 p-4 border border-gray-200 dark:border-gray-700 rounded-md">
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 mb-4">{{ __('Payment Proofs') }}</h3>
                                
                                @if($paymentId && count($existing_proofs) > 0)
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-400 mb-2">{{ __('Existing Proofs') }}:</p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach($existing_proofs as $proof)
                                                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded">
                                                    <a href="{{ Storage::url($proof->file_path) }}" target="_blank" class="text-sm text-blue-600 hover:underline flex items-center gap-2 truncate">
                                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                        {{ $proof->original_name ?: 'File' }}
                                                    </a>
                                                    <button type="button" wire:click="deleteProof({{ $proof->id }})" wire:confirm="{{ __('Delete this proof?') }}" class="text-red-500 hover:text-red-700 ml-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <x-input-label for="new_proofs" :value="__('Upload New Proofs')" />
                                    <input type="file" id="new_proofs" wire:model="new_proofs" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300" accept=".jpg,.jpeg,.png,.pdf">
                                    <div wire:loading wire:target="new_proofs" class="text-xs text-blue-500 mt-1 italic">{{ __('Uploading files...') }}</div>
                                    <x-input-error :messages="collect($errors->get('new_proofs.*'))->flatten()->all()" class="mt-2" />
                                </div>
                            </div>

                        </div>

                        <div class="mt-8 flex items-center justify-end gap-3">
                            <a href="{{ route('payments.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 text-sm font-medium">{{ __('Cancel') }}</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg transform transition active:scale-95">
                                {{ $paymentId ? __('Update Payment') : __('Complete Payment') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
