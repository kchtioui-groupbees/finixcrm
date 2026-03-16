<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex justify-between items-center">
            {{ __('My Payments') }}
            <span class="text-sm font-normal text-gray-500">
                {{ __('Total Contributed') }}: <span class="font-bold text-gray-900 dark:text-white">{{ $client ? $client->formatAmount($total_paid) : '$0.00' }}</span>
            </span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-xl font-bold mb-6">{{ __('Payment History') }}</h3>

                    @if(count($payments) === 0)
                        <div class="flex flex-col items-center py-12 text-center">
                            <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <p class="text-gray-500 font-medium italic">{{ __('No payments found in your account history') }}.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">{{ __('Date') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Reference / Product') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Amount') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Method') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Status') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                                                </div>
                                                <div class="text-xs text-gray-400">#P-{{ $payment->id }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($payment->type === 'specific_order' && $payment->order)
                                                    <a href="{{ route('client.products.show', $payment->order_id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold">
                                                        {{ $payment->order->product->name }}
                                                    </a>
                                                @elseif($payment->type === 'balance')
                                                    <div class="flex flex-col">
                                                        <span class="text-xs uppercase font-extrabold text-amber-600 dark:text-amber-500">{{ __('Balance Allocation') }}</span>
                                                        <div class="mt-1 space-y-0.5">
                                                            @foreach($payment->allocations as $allocation)
                                                                <div class="text-[10px] text-gray-500 flex items-center gap-1">
                                                                    <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                                    {{ $allocation->order->product->name ?? 'Service' }} 
                                                                    <span class="font-bold">({{ $client->formatAmount($allocation->amount) }})</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 italic">{{ __('No linked product') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-base font-black text-gray-900 dark:text-white">
                                                    {{ $client->formatAmount($payment->amount) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-xs text-gray-500 uppercase tracking-tight">
                                                    {{ __(str_replace('_', ' ', $payment->payment_method)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $statusClass = match($payment->status) {
                                                        'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800',
                                                        'failed' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    };
                                                @endphp
                                                <span class="{{ $statusClass }} text-[10px] font-black uppercase px-2 py-0.5 rounded-full">
                                                    {{ __($payment->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($payment->proofs->count() > 0)
                                                    <div class="flex items-center gap-2">
                                                        @foreach($payment->proofs as $proof)
                                                            <a href="{{ Storage::url($proof->file_path) }}" target="_blank" class="p-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition" title="View Attachment">
                                                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-[10px] text-gray-400 italic">{{ __('No receipt') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
