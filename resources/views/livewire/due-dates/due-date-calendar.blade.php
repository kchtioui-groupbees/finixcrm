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

                    <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
                        <div class="flex bg-gray-100 dark:bg-gray-900 p-1 rounded-lg">
                            <a href="{{ route('due-dates.index') }}" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500">{{ __('List') }}</a>
                            <a href="{{ route('due-dates.calendar') }}" class="px-4 py-1.5 text-xs font-bold rounded-md bg-white dark:bg-gray-700 shadow-sm">{{ __('Calendar') }}</a>
                        </div>

                        <div class="flex items-center gap-3">
                            <select wire:model.live="product_id" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                <option value="">{{ __('All products') }}</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>

                            <div class="flex items-center gap-2">
                                <button wire:click="previousMonth" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-gray-50">&larr;</button>
                                <span class="font-bold text-sm w-32 text-center">{{ $monthLabel }}</span>
                                <button wire:click="nextMonth" class="w-8 h-8 flex items-center justify-center rounded-full border border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-gray-50">&rarr;</button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-2 text-center text-[10px] font-black uppercase text-gray-400 mb-2">
                        @foreach([__('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun')] as $label)
                            <div>{{ $label }}</div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-7 gap-2">
                        @for($i = 0; $i < $leadingBlanks; $i++)
                            <div></div>
                        @endfor

                        @foreach($days as $day)
                            <button
                                wire:click="selectDate('{{ $day['date'] }}')"
                                @class([
                                    'aspect-square rounded-xl border p-2 flex flex-col items-start justify-between text-left transition-colors',
                                    'border-indigo-500 ring-2 ring-indigo-200' => $selectedDate === $day['date'],
                                    'border-gray-100 dark:border-gray-700' => $selectedDate !== $day['date'],
                                    'bg-indigo-50/50 dark:bg-indigo-900/10' => $day['is_today'],
                                    'bg-white dark:bg-gray-900' => !$day['is_today'],
                                ])
                            >
                                <span class="text-xs font-bold {{ $day['is_today'] ? 'text-indigo-600' : 'text-gray-500' }}">{{ $day['day'] }}</span>
                                @if($day['count'] > 0)
                                    <div class="w-full">
                                        <div class="text-[10px] font-black text-slate-700 dark:text-slate-200">{{ $day['count'] }} {{ __('due') }}</div>
                                        <div class="text-[9px] font-bold text-emerald-600">{{ number_format($day['total'], 2) }}</div>
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    @if($selectedDate)
                        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <h4 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">
                                {{ __('Due on') }} {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
                            </h4>
                            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($selectedOrders as $order)
                                    <div class="py-3 flex items-center justify-between">
                                        <div>
                                            <div class="font-bold text-sm text-gray-900 dark:text-gray-100">{{ $order->client->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">{{ $order->product->name ?? __('Unknown') }}</div>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <span class="font-bold text-sm">{{ $order->formatAmount($order->renewal_price ?? 0) }}</span>
                                            <button wire:click="$dispatch('open-renew-modal', { orderId: {{ $order->id }} })" class="text-xs font-bold text-emerald-600 uppercase tracking-wide">{{ __('Paid / Renew') }}</button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-gray-400 text-sm">{{ __('Nothing due this day.') }}</div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    @livewire('due-dates.renew-modal')
</div>
