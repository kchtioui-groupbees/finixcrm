<div>
    @if($show && $order)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-on:keydown.escape.window="$wire.close()">
            <div class="fixed inset-0 bg-gray-900/60 transition-opacity" wire:click="close"></div>

            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 mb-1">{{ __('Paid / Renew') }}</h3>
                    <p class="text-sm text-gray-500 mb-6">{{ __('Record this renewal payment and advance the next due date.') }}</p>

                    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase">{{ __('Client') }}</div>
                            <div class="font-bold text-gray-900 dark:text-gray-100">{{ $order->client->name }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase">{{ __('Product') }}</div>
                            <div class="font-bold text-gray-900 dark:text-gray-100">{{ $order->product->name }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase">{{ __('Current due date') }}</div>
                            <div class="font-bold text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($order->next_due_date)->format('d M Y') }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase">{{ __('Next due date will be') }}</div>
                            <div class="font-bold text-emerald-600">
                                {{ app(\App\Services\RenewalService::class)->calculateNextDueDate(\Carbon\Carbon::parse($order->next_due_date), $order->renewal_interval_unit, (int) $order->renewal_interval_value)->format('d M Y') }}
                            </div>
                        </div>
                    </div>

                    @php
                        $selectedMethod = $paymentMethods->firstWhere('key', $payment_method);
                    @endphp
                    @if($selectedMethod && $selectedMethod->requires_confirmation)
                        <div class="mb-4 flex items-start gap-2 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30 rounded-xl p-3 text-xs text-amber-700 dark:text-amber-400">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ __('This method requires manual confirmation. The payment will be recorded as pending and the due date will only advance once you confirm it was received.') }}</span>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="renew_amount" :value="__('Amount')" />
                            <input type="number" step="0.01" id="renew_amount" wire:model="amount" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                            <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="renew_payment_method" :value="__('Payment method')" />
                            <select id="renew_payment_method" wire:model.live="payment_method" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->key }}">{{ $method->label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('payment_method')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="renew_reference" :value="__('Reference (optional)')" />
                            <input type="text" id="renew_reference" wire:model="reference" placeholder="{{ __('e.g. WF839201, transfer receipt #...') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                            <x-input-error :messages="$errors->get('reference')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="renew_payment_date" :value="__('Payment date')" />
                            <input type="date" id="renew_payment_date" wire:model="payment_date" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                            <x-input-error :messages="$errors->get('payment_date')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="renew_notes" :value="__('Notes (optional)')" />
                            <textarea id="renew_notes" wire:model="internal_notes" rows="2" placeholder="{{ __('e.g. Virement BIAT annoncé à 14h20') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <button wire:click="stopRenewal" wire:confirm="{{ __('Stop renewal for this order? The product itself will not be affected.') }}" class="text-xs font-bold text-red-500 hover:underline uppercase tracking-wide">
                            {{ __('Stop renewal') }}
                        </button>
                        <div class="flex gap-3">
                            <button wire:click="close" class="text-sm font-bold text-gray-500">{{ __('Cancel') }}</button>
                            <button wire:click="confirmRenewal" class="btn-phoenix px-6 h-10 text-xs">{{ $selectedMethod && $selectedMethod->requires_confirmation ? __('Record pending payment') : __('Confirm payment') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
