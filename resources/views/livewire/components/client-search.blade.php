<div class="relative">
    @if($selectedClientId)
        <div class="flex items-center justify-between p-3.5 bg-indigo-50 border border-indigo-200 rounded-xl shadow-sm transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-black text-xs">
                    {{ strtoupper(substr($selectedClientName, 0, 1)) }}
                </div>
                <div>
                    <div class="text-sm font-black text-indigo-900 leading-tight">{{ $selectedClientName }}</div>
                    <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">{{ __('Active Selection') }}</div>
                </div>
            </div>
            <button type="button" wire:click="clearSelection" class="p-2 text-indigo-300 hover:text-indigo-600 hover:bg-white rounded-lg transition-colors border border-transparent hover:border-indigo-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @else
        <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="{{ __('Search client by name, email, or phone...') }}" 
                   class="w-full text-sm font-medium rounded-xl border-slate-200 bg-slate-50/50 focus:border-indigo-500 focus:ring-0 transition-all py-3.5 pl-11 shadow-sm"
                   autocomplete="off">

            @if($showDropdown && count($results) > 0)
                <div class="absolute z-50 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl shadow-slate-200/50 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
                    <div class="max-h-[300px] overflow-y-auto py-2">
                        @foreach($results as $client)
                            <button type="button" 
                                    wire:click="selectClient({{ $client->id }})" 
                                    class="w-full text-left px-5 py-3 hover:bg-slate-50 flex items-center justify-between transition-colors border-b border-slate-50 last:border-0 group/item">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center font-black text-slate-400 group-hover/item:bg-indigo-600 group-hover/item:text-white transition-colors">
                                        {{ strtoupper(substr($client->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-slate-900 group-hover/item:text-indigo-600 transition-colors">{{ $client->name }}</div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $client->email }}</div>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-slate-300 opacity-0 group-hover/item:opacity-100 transition-all group-hover/item:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        @endforeach
                    </div>
                </div>
            @elseif($showDropdown && count($results) === 0)
                <div class="absolute z-50 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl p-8 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-3 text-slate-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div class="text-sm font-bold text-slate-500">{{ __('No clients found matching your search.') }}</div>
                    <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest">{{ __('Try a different name or email') }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
