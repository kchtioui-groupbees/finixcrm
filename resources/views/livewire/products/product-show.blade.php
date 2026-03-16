<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('products.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Product Management') }}: {{ $product->name }}
                </h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('products.fields', $product) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    {{ __('Configure Fields') }}
                </a>
                <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition">
                    {{ __('Edit Base Info') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                <!-- Dynamic Sidebar Filters -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-500">{{ __('Smart Filters') }}</h3>
                            <button wire:click="resetFilters" class="text-[10px] text-indigo-600 font-bold hover:underline uppercase">{{ __('Reset') }}</button>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">{{ __('Client / Email') }}</label>
                                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search client...') }}" class="w-full text-sm rounded-md border-gray-200 dark:border-gray-700 dark:bg-gray-900">
                            </div>

                            @foreach($product->fields as $field)
                                @if(in_array($field->type, ['text', 'email', 'select', 'number']))
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">{{ $field->label }}</label>
                                        @if($field->type === 'select')
                                            <select wire:model.live="filters.{{ $field->id }}" class="w-full text-sm rounded-md border-gray-200 dark:border-gray-700 dark:bg-gray-900">
                                                <option value="">{{ __('All') }}</option>
                                                @foreach($field->options_json as $opt)
                                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="{{ $field->type }}" wire:model.live.debounce.300ms="filters.{{ $field->id }}" placeholder="{{ __('Filter by') }} {{ strtolower($field->label) }}..." class="w-full text-sm rounded-md border-gray-200 dark:border-gray-700 dark:bg-gray-900">
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-4">{{ __('Product Policy') }}</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">{{ __('Cashback Strategy') }}</label>
                                @if($product->cashback_enabled)
                                    <div class="text-sm font-black text-emerald-600 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path></svg>
                                        {{ $product->cashback_type === 'percentage' ? $product->cashback_value . '%' : '$' . number_format($product->cashback_value, 2) }} {{ __('Enabled') }}
                                    </div>
                                @else
                                    <div class="text-sm font-medium text-gray-300 italic">{{ __('Cashback Disabled') }}</div>
                                @endif
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">{{ __('System Protection') }}</label>
                                @if($product->warranty_enabled)
                                    <div class="text-sm font-black text-indigo-500 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.9L10 1.554L17.834 4.9c.456.19.76.63.76 1.126v.492c0 3.253-1.402 6.327-3.858 8.441a10.02 10.02 0 01-4.736 2.041a10.02 10.02 0 01-4.736-2.041C3.002 12.845 1.6 9.771 1.6 6.518V6.026c0-.495.304-.937.76-1.126zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path></svg>
                                        {{ $product->warranty_duration_days }} {{ __('Days Protection') }}
                                    </div>
                                @else
                                    <div class="text-sm font-medium text-gray-300 italic">{{ __('No Warranty') }}</div>
                                @endif
                            </div>
                        </div>

                    <div class="bg-indigo-600 rounded-xl shadow-sm p-6 text-white">
                        <div class="text-xs font-black uppercase tracking-widest opacity-70 mb-1">{{ __('Total Orders') }}</div>
                        <div class="text-3xl font-black">{{ $orders->total() }}</div>
                        <div class="mt-4 pt-4 border-t border-indigo-500 flex justify-between text-[10px] font-bold uppercase">
                            <span>{{ __('Active Ratio') }}</span>
                            <span>{{ number_format(($product->orders()->where('status', 'active')->count() / max(1, $orders->total())) * 100, 1) }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Main Order List -->
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <h3 class="font-bold text-gray-900 dark:text-gray-100">{{ __('Orders using this product') }}</h3>
                            <a href="{{ route('orders.create', ['product_id' => $product->id]) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-widest">+ {{ __('New Order') }}</a>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th class="px-6 py-3">{{ __('Client') }}</th>
                                        <th class="px-6 py-3">{{ __('Custom Data') }}</th>
                                        <th class="px-6 py-3 text-right">{{ __('Pending') }}</th>
                                        <th class="px-6 py-3 text-center">{{ __('Status') }}</th>
                                        <th class="px-6 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse($orders as $order)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition cursor-pointer" onclick="window.location='{{ route('orders.edit', $order) }}'">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-gray-900 dark:text-white">{{ $order->client->name }}</div>
                                                <div class="text-xs text-gray-400">{{ $order->client->email }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($order->fieldValues->take(3) as $fv)
                                                        @if($fv->value)
                                                            <div class="flex flex-col">
                                                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">{{ $fv->field->label }}</span>
                                                                <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-400">{{ \Illuminate\Support\Str::limit($fv->value, 20) }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                @if($order->pending_amount > 0)
                                                    <span class="font-black text-rose-600">{{ $order->formatAmount($order->pending_amount) }}</span>
                                                @else
                                                    <span class="text-emerald-600">{{ __('Paid') }} 💸</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase {{ $order->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ __($order->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                                {{ __('No orders found matching your filters') }}.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-6 border-t border-gray-100 dark:border-gray-700">
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
