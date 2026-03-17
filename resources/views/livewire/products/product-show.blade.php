<div>
    <x-slot name="header">
        <div class="max-w-[1100px] mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('products.index') }}" class="p-2 -ml-2 text-slate-400 hover:text-slate-600 transition-colors bg-white rounded-xl border border-slate-100 shadow-sm group">
                    <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">
                        {{ $product->name }}
                    </h2>
                    <p class="text-sm font-medium text-slate-400">{{ __('Product Management Dashboard') }}</p>
                </div>
            </div>
            
            <div class="flex gap-3 w-full md:w-auto">
                <a href="{{ route('products.fields', $product) }}" class="flex-1 md:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600/10 text-indigo-700 rounded-xl font-extrabold text-sm hover:bg-indigo-600/20 transition-all border border-indigo-100">
                    {{ __('Configure Fields') }}
                </a>
                <a href="{{ route('products.edit', $product) }}" class="flex-1 md:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-white text-slate-700 rounded-xl font-extrabold text-sm hover:bg-slate-50 transition-all border border-slate-200 shadow-sm">
                    {{ __('Edit Base Info') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-[#F8FAFC]">
        <div class="max-w-[1100px] mx-auto px-6 space-y-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
                
                {{-- ─────────────────────── SIDEBAR FILTERS & INFO ─────────────────────── --}}
                <div class="space-y-6">
                    
                    {{-- Smart Filters Card --}}
                    <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-200/50 p-7">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-[11px] font-black uppercase tracking-[0.1em] text-slate-400">{{ __('Smart Filters') }}</h3>
                            <button wire:click="resetFilters" class="text-[11px] text-indigo-600 font-extrabold hover:text-indigo-800 transition-colors uppercase tracking-wider">{{ __('Reset') }}</button>
                        </div>
                        
                        <div class="space-y-5">
                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-400 uppercase mb-2 ml-1 tracking-wider">{{ __('Client Search') }}</label>
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="search" 
                                           placeholder="{{ __('Name or email...') }}" 
                                           class="w-full text-sm font-medium rounded-xl border-slate-200 bg-slate-50/50 focus:border-indigo-500 focus:ring-0 transition-all py-2.5 pl-4">
                                </div>
                            </div>

                            @foreach($product->fields as $field)
                                @if(in_array($field->type, ['text', 'email', 'select', 'number']))
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-400 uppercase mb-2 ml-1 tracking-wider">{{ $field->label }}</label>
                                        @if($field->type === 'select')
                                            <select wire:model.live="filters.{{ $field->id }}" 
                                                    class="w-full text-sm font-medium rounded-xl border-slate-200 bg-slate-50/50 focus:border-indigo-500 focus:ring-0 transition-all py-2.5">
                                                <option value="">{{ __('All') }}</option>
                                                @foreach($field->options_json as $opt)
                                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="{{ $field->type }}" 
                                                   wire:model.live.debounce.300ms="filters.{{ $field->id }}" 
                                                   placeholder="{{ __('Filter by') }} {{ strtolower($field->label) }}..." 
                                                   class="w-full text-sm font-medium rounded-xl border-slate-200 bg-slate-50/50 focus:border-indigo-500 focus:ring-0 transition-all py-2.5 pl-4">
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    
                    {{-- Product Policy Card --}}
                    <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-200/50 p-7">
                        <h3 class="text-[11px] font-black uppercase tracking-[0.1em] text-slate-400 mb-6">{{ __('Product Policy') }}</h3>
                        <div class="space-y-5">
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 transition-all hover:border-emerald-100 group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase mb-2 tracking-widest">{{ __('Cashback') }}</label>
                                @if($product->cashback_enabled)
                                    <div class="text-sm font-black text-emerald-600 flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                        {{ $product->cashback_type === 'percentage' ? $product->cashback_value . '%' : '$' . number_format($product->cashback_value, 2) }}
                                        <span class="text-[10px] opacity-70 font-bold ml-auto">{{ __('Active') }}</span>
                                    </div>
                                @else
                                    <div class="text-sm font-bold text-slate-300 italic">{{ __('Disabled') }}</div>
                                @endif
                            </div>

                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 transition-all hover:border-indigo-100 group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase mb-2 tracking-widest">{{ __('Warranty') }}</label>
                                @if($product->warranty_enabled)
                                    <div class="text-sm font-black text-indigo-600 flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                                        {{ $product->warranty_duration_days }} {{ __('Days') }}
                                        <span class="text-[10px] opacity-70 font-bold ml-auto">{{ __('Protected') }}</span>
                                    </div>
                                @else
                                    <div class="text-sm font-bold text-slate-300 italic">{{ __('No Warranty') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Stats Area --}}
                    <div class="bg-slate-900 rounded-[1.5rem] shadow-xl shadow-slate-200 p-7 text-white relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/20 blur-3xl group-hover:bg-indigo-500/40 transition-colors"></div>
                        <div class="relative z-10">
                            <div class="text-[11px] font-black uppercase tracking-[0.15em] opacity-50 mb-1">{{ __('Total Orders') }}</div>
                            <div class="text-4xl font-black mb-6">{{ $orders->total() }}</div>
                            
                            <div class="pt-5 border-t border-white/10 flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black uppercase opacity-40 tracking-widest mb-1">{{ __('Active Ratio') }}</span>
                                    <span class="text-lg font-black text-emerald-400">{{ number_format(($product->orders()->where('status', 'active')->count() / max(1, $orders->total())) * 100, 1) }}%</span>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white/30" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─────────────────────── MAIN ORDER LIST ─────────────────────── --}}
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                        
                        {{-- Table Header --}}
                        <div class="p-8 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <h3 class="font-black text-xl text-slate-900 tracking-tight">{{ __('Orders Audit') }}</h3>
                                <p class="text-xs font-medium text-slate-400 mt-1">{{ __('List of all active and past client instance logs.') }}</p>
                            </div>
                            <a href="{{ route('orders.create', ['product_id' => $product->id]) }}" 
                               class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:scale-[1.03] transition-transform shadow-lg shadow-slate-200">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                {{ __('New Order') }}
                            </a>
                        </div>
                        
                        {{-- Data Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/50">
                                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('Client') }}</th>
                                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('Configuration') }}</th>
                                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">{{ __('Billing') }}</th>
                                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">{{ __('Status') }}</th>
                                        <th class="px-8 py-4"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($orders as $order)
                                        <tr class="group hover:bg-slate-50/70 transition-colors cursor-pointer" onclick="window.location='{{ route('orders.edit', $order) }}'">
                                            <td class="px-8 py-6">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center font-black text-indigo-600 text-sm group-hover:scale-110 transition-transform">
                                                        {{ strtoupper(substr($order->client->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-black text-slate-900 text-sm">{{ $order->client->name }}</div>
                                                        <div class="text-xs font-medium text-slate-400">{{ $order->client->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6">
                                                <div class="flex flex-wrap gap-3">
                                                    @foreach($order->fieldValues->take(3) as $fv)
                                                        @if($fv->value)
                                                            <div class="px-3 py-1.5 rounded-lg bg-white border border-slate-100 shadow-sm flex flex-col">
                                                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-tighter leading-none mb-1">{{ \Illuminate\Support\Str::limit($fv->field->label, 12) }}</span>
                                                                <span class="text-xs font-extrabold text-slate-700 leading-none">{{ \Illuminate\Support\Str::limit($fv->value, 15) }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-8 py-6 text-right">
                                                @if($order->pending_amount > 0)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 text-xs font-black border border-rose-100 italic">
                                                        {{ $order->formatAmount($order->pending_amount) }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-black border border-emerald-100">
                                                        {{ __('Paid') }} 💳
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-8 py-6 text-center">
                                                <span class="inline-flex px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $order->status === 'active' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-100' : 'bg-slate-100 text-slate-500' }}">
                                                    {{ __($order->status) }}
                                                </span>
                                            </td>
                                            <td class="px-8 py-6 text-right">
                                                <div class="p-2 rounded-lg bg-white border border-slate-100 text-slate-300 opacity-0 group-hover:opacity-100 transition-all group-hover:translate-x-1 shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-8 py-20 text-center">
                                                <div class="flex flex-col items-center">
                                                    <div class="w-16 h-16 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-200 mb-4">
                                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                    </div>
                                                    <p class="text-sm font-bold text-slate-400 capitalize">{{ __('No orders matching your criteria.') }}</p>
                                                    <button wire:click="resetFilters" class="text-xs font-black text-indigo-600 mt-2 uppercase tracking-widest hover:underline">{{ __('Clear all filters') }}</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Pagination --}}
                        @if($orders->count() > 0)
                            <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
                                {{ $orders->links() }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
