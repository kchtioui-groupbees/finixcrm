<div>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ __('Product Catalog') }}</h1>
                    <p class="text-slate-500 font-medium">{{ __('Manage your inventory and service offerings') }}.</p>
                </div>
                <a href="{{ route('products.create') }}" class="btn-phoenix">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    {{ __('Add New Product') }}
                </a>
            </div>

            @if (session()->has('message'))
                <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold text-sm">{{ session('message') }}</span>
                </div>
            @endif

            <div class="premium-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-500">
                        <thead class="text-[10px] font-black uppercase tracking-widest text-slate-400 bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-4">{{ __('Product Specs') }}</th>
                                <th class="px-6 py-4">{{ __('Inventory') }}</th>
                                <th class="px-6 py-4">{{ __('Unit Pricing') }}</th>
                                <th class="px-6 py-4">{{ __('Market Status') }}</th>
                                <th class="px-6 py-4 text-right">{{ __('Control') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($products as $product)
                                <tr class="hover:bg-slate-50 transition-colors group cursor-pointer" onclick="window.location='{{ route('products.show', $product) }}'">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-lg font-bold text-slate-400 group-hover:bg-finix-purple/10 group-hover:text-finix-purple transition-colors">
                                                {{ substr($product->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 group-hover:text-finix-purple transition-colors">{{ $product->name }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono tracking-tighter">{{ $product->sku ?: __('SKU-PENDING') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                         @if($product->stock_quantity <= $product->low_stock_threshold)
                                            <div class="flex items-center gap-1.5 text-amber-600 font-bold">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                                <span>{{ $product->stock_quantity }}</span>
                                                <span class="text-[8px] uppercase tracking-tighter bg-amber-100 px-1 rounded">{{ __('Low') }}</span>
                                            </div>
                                        @else
                                            <span class="text-slate-600 font-medium">{{ $product->stock_quantity }} {{ __('units') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-black text-slate-900">${{ number_format($product->price, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="badge-premium {{ $product->is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                                            {{ $product->is_active ? __('In Market') : __('Archived') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('products.edit', $product) }}" class="p-2 hover:bg-white rounded-lg border border-transparent hover:border-slate-200 text-slate-400 hover:text-finix-purple transition-all" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium italic">
                                        {{ __('No products found in the catalog') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
