<div>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ __('Client Directory') }}</h1>
                    <p class="text-slate-500 font-medium">{{ __('Manage your relationships and accounts') }}.</p>
                </div>
                <a href="{{ route('clients.create') }}" class="btn-phoenix">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    {{ __('New Client Relation') }}
                </a>
            </div>

            @if (session()->has('message'))
                <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold text-sm">{{ session('message') }}</span>
                </div>
            @endif

            <div class="premium-card overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/30">
                    <div class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex-1 max-w-md relative">
                            <x-input-label for="search" :value="__('Search Identity')" class="text-[10px] uppercase font-black text-slate-400 mb-1.5 ml-1 tracking-widest" />
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </span>
                                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Name, email, phone...') }}" class="input-premium pl-11 shadow-sm h-12">
                            </div>
                        </div>

                        <div class="w-full md:w-48">
                            <x-input-label for="balanceFilter" :value="__('Account Health')" class="text-[10px] uppercase font-black text-slate-400 mb-1.5 ml-1 tracking-widest" />
                            <select wire:model.live="balanceFilter" class="w-full h-12 rounded-xl border-slate-200 bg-white text-sm font-bold focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all">
                                <option value="">{{ __('All Balances') }}</option>
                                <option value="positive">{{ __('Positive Balance') }}</option>
                                <option value="negative">{{ __('Outstanding Debt') }}</option>
                                <option value="zero">{{ __('Settled / Zero') }}</option>
                            </select>
                        </div>

                        <button wire:click="resetFilters" class="h-12 px-5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors border border-slate-200 rounded-xl hover:bg-slate-50">
                            {{ __('Reset') }}
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-500 border-collapse">
                        <thead class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 bg-slate-50/50">
                            <tr>
                                <th class="px-8 py-5">{{ __('Client Identity') }}</th>
                                <th class="px-8 py-5">{{ __('Tags') }}</th>
                                <th class="px-8 py-5 text-right">{{ __('Financial Health') }}</th>
                                <th class="px-8 py-5 text-center">{{ __('Quick Ops') }}</th>
                                <th class="px-8 py-5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($clients as $client)
                                <tr class="hover:bg-slate-50/70 transition-colors group cursor-pointer" onclick="window.location='{{ route('clients.show', $client) }}'">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-11 h-11 rounded-2xl bg-slate-100 flex items-center justify-center font-black text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                                                {{ strtoupper(substr($client->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-black text-slate-900 group-hover:text-indigo-600 transition-all text-base">{{ $client->name }}</div>
                                                <div class="text-[11px] text-slate-400 font-bold flex items-center gap-1.5 mt-0.5">
                                                    <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                                    {{ $client->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-wrap gap-1.5">
                                            @if(is_array($client->tags))
                                                @forelse(array_slice($client->tags, 0, 2) as $tag)
                                                    <span class="px-2.5 py-1 text-[9px] font-black uppercase tracking-widest bg-slate-100 text-slate-500 rounded-lg border border-slate-200/50">{{ $tag }}</span>
                                                @empty
                                                    <span class="text-[10px] text-slate-300 italic font-bold tracking-tight">{{ __('No tags') }}</span>
                                                @endforelse
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="font-black text-lg {{ $client->credit_balance > 0 ? 'text-emerald-500' : ($client->credit_balance < 0 ? 'text-rose-500' : 'text-slate-400') }}">
                                            {{ $client->formatAmount($client->credit_balance) }}
                                        </div>
                                        <div class="text-[9px] font-black uppercase tracking-widest {{ $client->credit_balance > 0 ? 'text-emerald-300' : ($client->credit_balance < 0 ? 'text-rose-300' : 'text-slate-300') }}">
                                            {{ $client->credit_balance > 0 ? __('Credit Available') : ($client->credit_balance < 0 ? __('Outstanding') : __('Balanced')) }}
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center" onclick="event.stopPropagation()">
                                        <div class="flex justify-center gap-1.5">
                                            <a href="{{ route('orders.create', ['client_id' => $client->id]) }}" class="p-2 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm group/btn" title="{{ __('Quick Order') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                            </a>
                                            <a href="{{ route('payments.create', ['client_id' => $client->id]) }}" class="p-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition-all shadow-sm group/btn" title="{{ __('Quick Payment') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="p-2.5 rounded-xl bg-white border border-slate-100 text-slate-300 opacity-0 group-hover:opacity-100 transition-all group-hover:translate-x-1 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-24 text-center">
                                        <div class="w-16 h-16 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-4 text-slate-200">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        </div>
                                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ __('No clients matched your criteria') }}</p>
                                        <button wire:click="resetFilters" class="mt-2 text-xs font-black text-indigo-600 hover:underline uppercase tracking-tighter">{{ __('Clear all filters') }}</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $clients->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
