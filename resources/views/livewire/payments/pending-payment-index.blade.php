<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pending Payments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex bg-gray-100 dark:bg-gray-900 p-1 rounded-lg">
                            <a href="{{ route('payments.index') }}" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500">{{ __('All Payments') }}</a>
                            <a href="{{ route('payments.pending') }}" class="px-4 py-1.5 text-xs font-bold rounded-md bg-white dark:bg-gray-700 shadow-sm">{{ __('Pending Confirmation') }}</a>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search client, product, reference, note...') }}" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm lg:col-span-2">

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

                        <select wire:model.live="payment_method" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">{{ __('All methods') }}</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->key }}">{{ $method->label }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="olderThanDays" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">{{ __('Any age') }}</option>
                            <option value="1">{{ __('Older than 1 day') }}</option>
                            <option value="3">{{ __('Older than 3 days') }}</option>
                            <option value="7">{{ __('Older than 7 days') }}</option>
                        </select>

                        <input type="date" wire:model.live="date_from" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="{{ __('From') }}">
                        <input type="date" wire:model.live="date_to" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="{{ __('To') }}">
                        <input type="number" step="0.01" wire:model.live.debounce.300ms="min_amount" placeholder="{{ __('Min amount') }}" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                        <input type="number" step="0.01" wire:model.live.debounce.300ms="max_amount" placeholder="{{ __('Max amount') }}" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                    </div>

                    <!-- Desktop table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">{{ __('Date') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Client') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Order / Subscription') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Amount') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Method') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Reference') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Note') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Age') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    @php $ageDays = (int) $payment->created_at->diffInDays(now()); @endphp
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $payment->payment_date?->format('d M Y') }}</td>
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $payment->client->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ $payment->order?->product?->name ?? __('Balance') }}</td>
                                        <td class="px-6 py-4 font-bold">{{ $payment->formatAmount($payment->amount) }}</td>
                                        <td class="px-6 py-4">{{ $paymentMethods->firstWhere('key', $payment->payment_method)?->label ?? $payment->payment_method }}</td>
                                        <td class="px-6 py-4 text-xs">{{ $payment->reference ?: '—' }}</td>
                                        <td class="px-6 py-4 text-xs max-w-[180px] truncate" title="{{ $payment->internal_notes }}">{{ $payment->internal_notes ?: '—' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="text-xs font-bold px-2 py-0.5 rounded {{ $ageDays >= 3 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $ageDays }} {{ __('d') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex gap-3">
                                                <button wire:click="confirm({{ $payment->id }})" wire:confirm="{{ __('Confirm this payment was received?') }}" class="text-xs font-bold text-emerald-600 uppercase tracking-wide">{{ __('Confirm') }}</button>
                                                <button wire:click="reject({{ $payment->id }})" wire:confirm="{{ __('Reject this pending payment?') }}" class="text-xs font-bold text-red-600 uppercase tracking-wide">{{ __('Reject') }}</button>
                                                <a href="{{ route('payments.edit', $payment) }}" class="text-xs font-bold text-blue-600 uppercase tracking-wide">{{ __('View') }}</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">{{ __('No pending payments.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile cards -->
                    <div class="md:hidden space-y-3">
                        @forelse($payments as $payment)
                            @php $ageDays = (int) $payment->created_at->diffInDays(now()); @endphp
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-white dark:bg-gray-900">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $payment->client->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $payment->order?->product?->name ?? __('Balance') }}</div>
                                    </div>
                                    <span class="font-bold text-emerald-600">{{ $payment->formatAmount($payment->amount) }}</span>
                                </div>
                                <div class="text-xs text-gray-500 mb-1">{{ $paymentMethods->firstWhere('key', $payment->payment_method)?->label ?? $payment->payment_method }} &middot; {{ $payment->payment_date?->format('d M Y') }}</div>
                                @if($payment->reference)
                                    <div class="text-xs text-gray-500 mb-1">{{ __('Ref') }}: {{ $payment->reference }}</div>
                                @endif
                                @if($payment->internal_notes)
                                    <div class="text-xs italic text-gray-400 mb-2">"{{ $payment->internal_notes }}"</div>
                                @endif
                                <div class="mb-3">
                                    <span class="text-xs font-bold px-2 py-0.5 rounded {{ $ageDays >= 3 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ __('Pending') }} {{ $ageDays }} {{ __('day(s)') }}
                                    </span>
                                </div>
                                <div class="flex gap-4">
                                    <button wire:click="confirm({{ $payment->id }})" wire:confirm="{{ __('Confirm this payment was received?') }}" class="text-xs font-bold text-emerald-600 uppercase tracking-wide">{{ __('Confirm') }}</button>
                                    <button wire:click="reject({{ $payment->id }})" wire:confirm="{{ __('Reject this pending payment?') }}" class="text-xs font-bold text-red-600 uppercase tracking-wide">{{ __('Reject') }}</button>
                                    <a href="{{ route('payments.edit', $payment) }}" class="text-xs font-bold text-blue-600 uppercase tracking-wide">{{ __('View') }}</a>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-500">{{ __('No pending payments.') }}</div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $payments->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
