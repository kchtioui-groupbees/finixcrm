<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex justify-between items-center">
            <span>{{ __('Transactions for') }} {{ $client->name }}</span>
            <div class="flex items-center gap-4">
                <div class="px-4 py-1 bg-green-100 dark:bg-green-900/40 border border-green-200 rounded-lg text-green-800 dark:text-green-300">
                    <span class="text-xs uppercase font-bold">Credit Balance:</span>
                    <span class="text-lg font-black ml-1">${{ number_format($client->credit_balance, 2) }}</span>
                </div>
                <button wire:click="$set('showApplyModal', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow-sm text-sm">
                    Apply Credit to Order
                </button>
                <button wire:click="$set('showManualModal', true)" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow-sm text-sm">
                    Manual Adjustment
                </button>
            </div>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session()->has('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('message') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">Balance Ledger</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3">Date</th>
                                    <th class="px-6 py-3">Type</th>
                                    <th class="px-6 py-3">Description</th>
                                    <th class="px-6 py-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($transactions as $tx)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $tx->created_at->format('d M Y H:i') }}</td>
                                        <td class="px-6 py-4 px-6">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] uppercase font-bold
                                                {{ $tx->amount > 0 ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}
                                            ">
                                                {{ str_replace('_', ' ', $tx->type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                            {{ $tx->description }}
                                            @if($tx->payment_id)
                                                <a href="{{ route('payments.edit', $tx->payment_id) }}" class="text-blue-500 hover:underline ml-1">#P{{ $tx->payment_id }}</a>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right font-mono font-bold {{ $tx->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $tx->amount > 0 ? '+' : '' }}{{ number_format($tx->amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 italic">No transactions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Apply Credit Modal -->
    @if($showApplyModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Apply Available Credit</h3>
                
                @if($client->credit_balance <= 0)
                    <div class="p-4 bg-orange-50 text-orange-800 rounded mb-4 text-sm">
                        This client has no available credit.
                    </div>
                    <button wire:click="$set('showApplyModal', false)" class="w-full bg-gray-200 py-2 rounded font-bold">Close</button>
                @else
                    <form wire:submit.prevent="applyCredit">
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="order" :value="__('Select Unpaid Order')" />
                                <select id="order" wire:model.live="selectedOrderId" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                                    <option value="">-- Choose Order --</option>
                                    @foreach($unpaidOrders as $order)
                                        <option value="{{ $order->id }}">{{ $order->product->name }} (Due: ${{ number_format($order->pending_amount, 2) }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('selectedOrderId')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="amountToApply" :value="__('Amount to Apply')" />
                                <x-text-input id="amountToApply" type="number" step="0.01" wire:model="amountToApply" class="mt-1 block w-full" />
                                <div class="text-[10px] text-gray-500 mt-1 italic">Max available: ${{ number_format($client->credit_balance, 2) }}</div>
                                <x-input-error :messages="$errors->get('amountToApply')" class="mt-1" />
                            </div>

                            <div class="flex gap-3 mt-6">
                                <button type="button" wire:click="$set('showApplyModal', false)" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded font-bold">Cancel</button>
                                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded font-bold hover:bg-indigo-700">Apply Now</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif

    <!-- Manual Adjustment Modal -->
    @if($showManualModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Manual Balance Adjustment</h3>
                
                <form wire:submit.prevent="manualAdjustment">
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="manualAmount" :value="__('Amount (use minus for debit)')" />
                            <x-text-input id="manualAmount" type="number" step="0.01" wire:model="manualAmount" class="mt-1 block w-full" placeholder="e.g. 50.00 or -20.00" />
                            <x-input-error :messages="$errors->get('manualAmount')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="manualDescription" :value="__('Description / Reason')" />
                            <x-text-input id="manualDescription" type="text" wire:model="manualDescription" class="mt-1 block w-full" placeholder="Internal note..." />
                            <x-input-error :messages="$errors->get('manualDescription')" class="mt-1" />
                        </div>

                        <div class="flex gap-3 mt-6">
                            <button type="button" wire:click="$set('showManualModal', false)" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded font-bold">Cancel</button>
                            <button type="submit" class="flex-1 px-4 py-2 bg-gray-800 dark:bg-gray-900 text-white rounded font-bold hover:bg-black">Save Adjustment</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
