<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Due Dates') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- View toggle -->
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex bg-gray-100 dark:bg-gray-900 p-1 rounded-lg">
                            <a href="{{ route('due-dates.index') }}" class="px-4 py-1.5 text-xs font-bold rounded-md bg-white dark:bg-gray-700 shadow-sm">{{ __('List') }}</a>
                            <a href="{{ route('due-dates.calendar') }}" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500">{{ __('Calendar') }}</a>
                        </div>
                    </div>

                    <!-- Quick filters -->
                    <div class="mb-4 flex flex-wrap gap-2">
                        @foreach(['today' => __('Today'), 'week' => __('This week'), 'next7' => __('Next 7 days'), 'next30' => __('Next 30 days'), 'overdue' => __('Overdue')] as $key => $label)
                            <button
                                wire:click="setQuickFilter('{{ $key }}')"
                                class="px-3 py-1.5 text-xs font-bold rounded-full border transition-colors {{ $quickFilter === $key ? ($key === 'overdue' ? 'bg-red-600 text-white border-red-600' : 'bg-indigo-600 text-white border-indigo-600') : 'bg-white dark:bg-gray-900 text-gray-500 border-gray-200 dark:border-gray-700' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                        <button
                            wire:click="toggleUnconfigured"
                            class="px-3 py-1.5 text-xs font-bold rounded-full border transition-colors {{ $unconfigured ? 'bg-amber-500 text-white border-amber-500' : 'bg-white dark:bg-gray-900 text-gray-500 border-gray-200 dark:border-gray-700' }}"
                        >
                            {{ __('Unconfigured subscriptions') }}
                        </button>
                    </div>

                    <!-- Filters -->
                    <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search client or product...') }}" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">

                        <select wire:model.live="client_id" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">{{ __('All clients') }}</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="product_id" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">{{ __('All products') }}</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>

                        <input type="date" wire:model.live="date_from" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="{{ __('From') }}">
                        <input type="date" wire:model.live="date_to" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="{{ __('To') }}">
                    </div>

                    <!-- Desktop table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">{{ __('Due Date') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Client') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Product') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Amount') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Status') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 font-bold whitespace-nowrap">
                                            {{ $order->next_due_date ? \Carbon\Carbon::parse($order->next_due_date)->format('d M Y') : __('Not configured') }}
                                        </td>
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $order->client->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ $order->product->name ?? __('Unknown') }}</td>
                                        <td class="px-6 py-4">{{ $order->formatAmount($order->renewal_price ?? 0) }}</td>
                                        <td class="px-6 py-4">
                                            @if($order->is_overdue_renewal)
                                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">{{ __('Overdue') }}</span>
                                            @elseif($order->next_due_date && \Carbon\Carbon::parse($order->next_due_date)->isToday())
                                                <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-amber-900 dark:text-amber-300">{{ __('Due today') }}</span>
                                            @elseif($order->next_due_date)
                                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">{{ __('Upcoming') }}</span>
                                            @else
                                                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">{{ __('Unconfigured') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 flex gap-3">
                                            <a href="{{ route('orders.edit', $order) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">{{ __('Edit') }}</a>
                                            @if($order->next_due_date)
                                                <button wire:click="$dispatch('open-renew-modal', { orderId: {{ $order->id }} })" class="font-medium text-emerald-600 dark:text-emerald-500 hover:underline">{{ __('Paid / Renew') }}</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">{{ __('No due dates found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile cards -->
                    <div class="md:hidden space-y-3">
                        @forelse($orders as $order)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-white dark:bg-gray-900">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $order->client->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $order->product->name ?? __('Unknown') }}</div>
                                    </div>
                                    @if($order->is_overdue_renewal)
                                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-0.5 rounded dark:bg-red-900 dark:text-red-300">{{ __('Overdue') }}</span>
                                    @endif
                                </div>
                                <div class="flex justify-between items-center text-sm mb-3">
                                    <span class="font-bold">{{ $order->next_due_date ? \Carbon\Carbon::parse($order->next_due_date)->format('d M Y') : __('Not configured') }}</span>
                                    <span class="font-bold text-emerald-600">{{ $order->formatAmount($order->renewal_price ?? 0) }}</span>
                                </div>
                                <div class="flex gap-4">
                                    <a href="{{ route('orders.edit', $order) }}" class="text-xs font-bold text-blue-600">{{ __('Edit') }}</a>
                                    @if($order->next_due_date)
                                        <button wire:click="$dispatch('open-renew-modal', { orderId: {{ $order->id }} })" class="text-xs font-bold text-emerald-600 uppercase tracking-wide">{{ __('Paid / Renew') }}</button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-500">{{ __('No due dates found.') }}</div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    @livewire('due-dates.renew-modal')
</div>
