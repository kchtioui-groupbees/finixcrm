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
                    <div class="max-w-md relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search by name, email or phone...') }}" class="input-premium pl-11">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-500">
                        <thead class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <tr>
                                <th class="px-6 py-4">{{ __('Identity') }}</th>
                                <th class="px-6 py-4">{{ __('Portfolio Tags') }}</th>
                                <th class="px-6 py-4">{{ __('Account Balance') }}</th>
                                <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($clients as $client)
                                <tr class="hover:bg-slate-50 transition-colors group cursor-pointer" onclick="window.location='{{ route('clients.show', $client) }}'">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-black text-slate-400 group-hover:bg-finix-purple/10 group-hover:text-finix-purple transition-colors">
                                                {{ substr($client->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 group-hover:text-finix-purple transition-all">{{ $client->name }}</div>
                                                <div class="text-[10px] text-slate-400 font-medium">{{ $client->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @if(is_array($client->tags))
                                                @forelse($client->tags as $tag)
                                                    <span class="badge-premium bg-slate-100 text-slate-600 border-slate-200">{{ $tag }}</span>
                                                @empty
                                                    <span class="text-[10px] text-slate-300 italic font-medium">{{ __('Standard') }}</span>
                                                @endforelse
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-black {{ $client->credit_balance > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                                            {{ $client->formatAmount($client->credit_balance) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right" onclick="event.stopPropagation()">
                                        <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('clients.show', $client) }}" class="p-2 hover:bg-white rounded-lg border border-transparent hover:border-slate-200 text-slate-400 hover:text-finix-purple transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                            <a href="{{ route('clients.edit', $client) }}" class="p-2 hover:bg-white rounded-lg border border-transparent hover:border-slate-200 text-slate-400 hover:text-blue-500 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-medium italic">{{ __('No clients matches your criteria') }}.</td>
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
