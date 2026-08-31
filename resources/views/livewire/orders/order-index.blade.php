<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex justify-between items-center">
            <span>{{ __('Orders Management') }}</span>
            <a href="{{ route('orders.create') }}" class="btn-phoenix">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ __('New Order') }}
            </a>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="mb-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search by product or client...') }}" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full sm:w-1/3">
                    </div>

                    @php
                        $statusBadge = function ($status) {
                            return match(true) {
                                in_array($status, ['active', 'completed']) => ['bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', $status === 'completed' ? __('Completed') : __('Active')],
                                $status === 'partially_paid' => ['bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300', __('Partially Paid')],
                                $status === 'expiring_soon' => ['bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300', __('Expiring')],
                                $status === 'expired' => ['bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300', __('Expired')],
                                default => ['bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', __(ucfirst(str_replace('_', ' ', $status)))],
                            };
                        };
                    @endphp

                    <!-- Mobile cards -->
                    <div class="md:hidden space-y-3">
                        @forelse ($orders as $order)
                            @php [$badgeClass, $badgeLabel] = $statusBadge($order->status); @endphp
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-white dark:bg-gray-900">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $order->client->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $order->product->name ?? __('Unknown') }}</div>
                                    </div>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                </div>
                                <div class="text-xs text-gray-500 mb-1">{{ $order->formatAmount($order->price) }}</div>
                                <div class="text-xs text-gray-400 mb-3">
                                    {{ __('Pur') }}: {{ \Carbon\Carbon::parse($order->purchase_date)->format('Y-m-d') }} &middot;
                                    {{ __('Exp') }}: {{ \Carbon\Carbon::parse($order->expiry_date)->format('Y-m-d') }}
                                </div>
                                <div class="flex gap-4 pt-2 border-t border-gray-100 dark:border-gray-800">
                                    <a href="{{ route('orders.edit', $order) }}" class="text-xs font-bold text-blue-600 uppercase tracking-wide">{{ __('Edit') }}</a>
                                    <button wire:click="deleteOrder({{ $order->id }})" wire:confirm="{{ __('Are you sure you want to delete this order? All related payments will be lost.') }}" class="text-xs font-bold text-red-600 uppercase tracking-wide">{{ __('Delete') }}</button>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-500">{{ __('No orders found.') }}</div>
                        @endforelse
                    </div>

                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">{{ __('Client') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Product') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Price') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Dates') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Status') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Cashback') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr 
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors group"
                                        onclick="window.location='{{ route('orders.edit', $order) }}'"
                                    >
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $order->client->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4">{{ $order->product->name ?? __('Unknown') }}</td>
                                        <td class="px-6 py-4">{{ $order->formatAmount($order->price) }}</td>
                                        <td class="px-6 py-4 text-xs whitespace-nowrap">
                                            <div>{{ __('Pur') }}: {{ \Carbon\Carbon::parse($order->purchase_date)->format('Y-m-d') }}</div>
                                            <div class="text-gray-400">{{ __('Exp') }}: {{ \Carbon\Carbon::parse($order->expiry_date)->format('Y-m-d') }}</div>
                                        </td>
                                         <td class="px-6 py-4">
                                            @php [$badgeClass, $badgeLabel] = $statusBadge($order->status); @endphp
                                            <span class="text-xs font-medium px-2.5 py-0.5 rounded {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                        </td>
                                        <!-- Cashback badge -->
                                        <td class="px-6 py-4">
                                            @php $cbStatus = $order->cashback_status; @endphp
                                            @if($cbStatus === 'rewarded')
                                                <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-xs font-bold px-2 py-0.5 rounded-full dark:bg-emerald-900 dark:text-emerald-300">
                                                    ★ {{ $order->formatAmount($order->cashback_amount) }}
                                                </span>
                                            @elseif($cbStatus === 'pending_reward')
                                                <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-0.5 rounded-full dark:bg-yellow-900 dark:text-yellow-300">
                                                    ⏳ {{ $order->formatAmount($order->cashback_amount) }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 flex gap-2">
                                            <a href="{{ route('orders.edit', $order) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">{{ __('Edit') }}</a>
                                            <button wire:click="deleteOrder({{ $order->id }})" wire:confirm="{{ __('Are you sure you want to delete this order? All related payments will be lost.') }}" class="font-medium text-red-600 dark:text-red-500 hover:underline">{{ __('Delete') }}</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            {{ __('No orders found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
