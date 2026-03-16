<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex justify-between items-center">
            <span>{{ __('Payments Management') }}</span>
            <a href="{{ route('payments.create') }}" class="btn-phoenix">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ __('Log Payment') }}
            </a>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session()->has('message'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4" role="alert">
                    <p class="text-sm text-green-700">{{ session('message') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="mb-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search by client, product, amount or method...') }}" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full sm:w-1/3">
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">{{ __('Client & Allocation') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Mode') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Amount') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Method & Date') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Status') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Proofs') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payments as $payment)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $payment->client->name ?? __('Unknown Client') }}</div>
                                            @if($payment->type === 'specific_order' && $payment->order)
                                                <div class="text-[10px] text-gray-500 font-medium">{{ __('Applied to') }}: {{ $payment->order->product->name ?? __('Product') }}</div>
                                            @elseif($payment->type === 'balance')
                                                <div class="text-[10px] text-blue-500 font-medium">{{ __('Allocated across') }} {{ $payment->allocations->count() }} {{ __('services') }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($payment->type === 'balance')
                                                <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-bold uppercase tracking-wider">{{ __('Balance') }}</span>
                                            @else
                                                <span class="text-[10px] bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-bold uppercase tracking-wider">{{ __('Specific') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                            ${{ number_format($payment->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-xs">
                                            <div class="font-medium">{{ str_replace('_', ' ', ucfirst(__($payment->payment_method ?: __('N/A')))) }}</div>
                                            <div class="text-gray-400">{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : __('N/A') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($payment->status === 'completed')
                                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">{{ __('Completed') }}</span>
                                            @elseif($payment->status === 'pending')
                                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">{{ __('Pending') }}</span>
                                            @else
                                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">{{ __('Failed') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($payment->proofs && $payment->proofs->count() > 0)
                                                <span class="inline-flex items-center text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    {{ $payment->proofs->count() }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">{{ __('None') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 flex gap-3 text-xs">
                                            <a href="{{ route('payments.edit', $payment) }}" class="font-bold text-blue-600 dark:text-blue-500 hover:text-blue-800">{{ __('EDIT') }}</a>
                                            <button wire:click="deletePayment({{ $payment->id }})" wire:confirm="{{ __('Delete this payment record?') }}" class="font-bold text-red-600 dark:text-red-500 hover:text-red-800 uppercase">{{ __('Delete') }}</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            {{ __('No payments found matching your criteria.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $payments->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
