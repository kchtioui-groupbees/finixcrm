<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $orderId ? __('Edit Order') : __('Create Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-slate-200/60">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <form wire:submit="save" class="space-y-10">
                        
                        <!-- Section 1: Core Selection -->
                        <div class="space-y-6">
                            <h3 class="text-xs font-black text-indigo-500 uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ __('Relationship & Product') }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Client -->
                                <div>
                                    <x-input-label for="client_id" :value="__('Select Client')" class="text-[10px] uppercase font-bold text-slate-500 mb-1" />
                                    <div class="flex gap-2">
                                        <select id="client_id" wire:model.live="client_id" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-xl shadow-sm h-11" required>
                                            <option value="">-- {{ __('Choose a Client') }} --</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                                            @endforeach
                                        </select>
                                        <a href="{{ route('clients.create') }}" target="_blank" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-xl transition" title="{{ __('New Client') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        </a>
                                    </div>
                                    <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                                </div>

                                <!-- Product -->
                                <div>
                                    <x-input-label for="product_id" :value="__('Product / Template')" class="text-[10px] uppercase font-bold text-slate-500 mb-1" />
                                    <select id="product_id" wire:model.live="product_id" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-xl shadow-sm h-11" required>
                                        <option value="">-- {{ __('Choose a Product') }} --</option>
                                        @foreach($products as $productOption)
                                            <option value="{{ $productOption->id }}">{{ $productOption->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('product_id')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Product Specifications (Dynamic) -->
                        @if(count($dynamicFields) > 0)
                            <div class="space-y-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                                <h4 class="text-xs font-black text-rose-500 uppercase tracking-widest flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    {{ __('Product Specifications') }}
                                </h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/50 dark:bg-slate-900/40 p-6 rounded-2xl border border-slate-100 dark:border-slate-800">
                                    @foreach($dynamicFields as $field)
                                        <div class="{{ in_array($field->type, ['textarea']) ? 'md:col-span-2' : '' }}" wire:key="field-wrapper-{{ $field->id }}">
                                            <label for="field_{{ $field->id }}" class="block font-bold text-xs text-gray-700 dark:text-gray-300 mb-1">
                                                {{ $field->label }} 
                                                @if($field->is_required)<span class="text-red-500">*</span>@endif
                                                @if($field->is_admin_only)<span class="ml-1 text-[9px] px-1.5 py-0.5 bg-red-50 text-red-500 rounded-full font-black tracking-wider uppercase">({{ __('Admin Only') }})</span>@endif
                                            </label>
                                            
                                            @if($field->type === 'textarea')
                                                <textarea wire:model="dynamicFieldValues.{{ $field->id }}" id="field_{{ $field->id }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-xl shadow-sm" rows="3" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}></textarea>
                                            
                                            @elseif($field->type === 'select')
                                                <select wire:model="dynamicFieldValues.{{ $field->id }}" id="field_{{ $field->id }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-xl shadow-sm h-11" {{ $field->is_required ? 'required' : '' }}>
                                                    <option value="">-- {{ __('Select') }} --</option>
                                                    @if(is_array($field->options_json))
                                                        @foreach($field->options_json as $opt)
                                                             <option value="{{ trim($opt) }}">{{ trim($opt) }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            
                                            @elseif($field->type === 'checkbox')
                                                <div class="mt-2 bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200">
                                                    <label class="inline-flex items-center cursor-pointer">
                                                        <input wire:model="dynamicFieldValues.{{ $field->id }}" id="field_{{ $field->id }}" type="checkbox" class="w-5 h-5 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500 shadow-sm" value="1">
                                                        <span class="ms-3 text-sm font-bold text-gray-700 dark:text-gray-400">{{ __('Yes / Checked') }}</span>
                                                    </label>
                                                </div>
                                            @else
                                                <input wire:model="dynamicFieldValues.{{ $field->id }}" id="field_{{ $field->id }}" type="{{ $field->type }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-xl shadow-sm h-11" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}>
                                            @endif
                                            <x-input-error :messages="$errors->get('dynamicFieldValues.'.$field->id)" class="mt-2" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Section 3: Financial Details -->
                        <div class="space-y-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <h3 class="text-xs font-black text-emerald-500 uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ __('Order Price & Financials') }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                                <div>
                                   <x-input-label for="price" :value="__('Selling Price')" class="text-[10px] uppercase font-bold text-slate-400 mb-1" />
                                   <div class="relative">
                                       <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                           <span class="text-slate-400 font-bold group-focus-within:text-indigo-500 transition-colors">{{ $currency === 'TND' ? 'TND' : '$' }}</span>
                                       </div>
                                       <input id="price" type="number" step="0.001" class="pl-14 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-xl h-14 text-xl font-black focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" wire:model.live="price" required />
                                   </div>
                                   <x-input-error :messages="$errors->get('price')" class="mt-2" />
                                   
                                   <div class="mt-4">
                                        <x-input-label for="status" :value="__('Financial Status')" class="text-[10px] uppercase font-bold text-slate-400 mb-1" />
                                        <select id="status" wire:model="status" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-xl h-11 focus:ring-indigo-500 shadow-sm font-bold text-sm" required>
                                            <option value="active">{{ __('Active') }}</option>
                                            <option value="expiring_soon">{{ __('Expiring Soon') }}</option>
                                            <option value="expired">{{ __('Expired') }}</option>
                                            <option value="cancelled">{{ __('Cancelled') }}</option>
                                            <option value="completed">{{ __('Completed') }}</option>
                                            <option value="partially_paid">{{ __('Partially Paid') }}</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                   </div>
                                </div>

                                <div class="space-y-4">
                                    @if($client_id && $client_credit_balance > 0)
                                        <div class="bg-indigo-50 dark:bg-indigo-900/20 p-5 rounded-2xl border border-indigo-100 dark:border-indigo-800">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">{{ __('Available Credit') }}</span>
                                                <span class="text-lg font-black text-indigo-600 dark:text-indigo-300">{{ $this->formatAmount($client_credit_balance) }}</span>
                                            </div>
                                            @if($applied_credit > 0)
                                                <div class="pt-2 border-t border-indigo-200/50 flex justify-between items-center text-xs">
                                                    <span class="text-indigo-500/70 font-bold uppercase tracking-tighter">{{ __('Auto-applied:') }}</span>
                                                    <span class="font-black text-red-500">-{{ $this->formatAmount($applied_credit) }}</span>
                                                </div>
                                                <div class="mt-1 flex justify-between items-center text-sm font-black text-slate-900 dark:text-slate-100">
                                                    <span>{{ __('Net Due:') }}</span>
                                                    <span class="text-emerald-600">{{ $this->formatAmount(max(0, (float)$price - $applied_credit)) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if($this->estimated_cashback > 0)
                                        <div class="bg-emerald-50 dark:bg-emerald-900/10 p-5 rounded-2xl border border-emerald-100 dark:border-emerald-800/30 flex justify-between items-center group hover:bg-emerald-100/50 transition-colors duration-300">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12h14v10H5V12z"></path></svg>
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-[10px] font-black text-emerald-800 dark:text-emerald-400 uppercase tracking-widest">{{ __('Est. Cashback') }}</span>
                                                    <span class="text-[11px] text-emerald-600 font-bold">{{ __('Upon completion') }}</span>
                                                </div>
                                            </div>
                                            <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $this->formatAmount($this->estimated_cashback) }}</span>
                                        </div>
                                    @endif

                                    @if(!$client_id)
                                        <div class="p-6 border-2 border-dashed border-slate-100 rounded-2xl flex items-center justify-center text-slate-300 text-sm font-bold italic">
                                            {{ __('Select a client to see credit options') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Schedule & Duration -->
                        <div class="space-y-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <h3 class="text-xs font-black text-amber-500 uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ __('Dates & Duration') }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-5">
                                    <div>
                                        <x-input-label for="purchase_date" :value="__('Purchase Date')" class="text-[10px] uppercase font-bold text-slate-400 mb-1" />
                                        <x-text-input id="purchase_date" type="date" class="block w-full border-slate-200 rounded-xl h-11" wire:model.live="purchase_date" required />
                                    </div>

                                    <div class="bg-amber-50/30 dark:bg-amber-900/10 p-5 rounded-2xl border border-amber-100 dark:border-amber-800/30">
                                        <div class="flex items-center justify-between mb-4">
                                            <x-input-label for="expiry_mode" :value="__('Expiry Calculation')" class="text-[10px] font-black text-amber-600 uppercase" />
                                            <div class="flex bg-white dark:bg-slate-900 p-1 rounded-lg border border-slate-200">
                                                <button type="button" wire:click="$set('expiry_mode', 'manual')" class="px-3 py-1 text-[9px] font-black rounded-md transition-all {{ $expiry_mode === 'manual' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-400' }}">{{ __('Manual') }}</button>
                                                <button type="button" wire:click="$set('expiry_mode', 'calculate')" class="px-3 py-1 text-[9px] font-black rounded-md transition-all {{ $expiry_mode === 'calculate' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-400' }}">{{ __('Auto') }}</button>
                                            </div>
                                        </div>

                                        @if($expiry_mode === 'calculate')
                                            <div class="flex gap-2 mb-4">
                                                <input type="number" wire:model.live="expiry_value" class="block w-24 border-slate-200 rounded-xl h-10 text-sm font-bold" placeholder="Val">
                                                <select wire:model.live="expiry_unit" class="block flex-1 border-slate-200 rounded-xl h-10 text-xs font-bold">
                                                    <option value="days">{{ __('Days') }}</option>
                                                    <option value="months">{{ __('Months') }}</option>
                                                    <option value="years">{{ __('Years') }}</option>
                                                </select>
                                            </div>
                                        @endif

                                        <div class="relative">
                                            <x-input-label for="expiry_date" :value="__('Final Expiry Date')" class="text-[10px] font-bold text-slate-400 uppercase mb-1" />
                                            <x-text-input id="expiry_date" type="date" class="block w-full h-11 border-slate-200 rounded-xl font-bold {{ $expiry_mode === 'calculate' ? 'bg-amber-50/50 border-amber-100 text-amber-700' : '' }}" wire:model.live="expiry_date" required :readonly="$expiry_mode === 'calculate'" />
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <x-input-label for="duration" :value="__('Display Duration (Optional)')" class="text-[10px] uppercase font-bold text-slate-400 mb-1" />
                                        <x-text-input id="duration" type="text" class="block w-full border-slate-200 rounded-xl h-11" wire:model="duration" placeholder="e.g. 1 Year, 6 Months" />
                                    </div>
                                    
                                    <div>
                                        <x-input-label for="reminder_date" :value="__('Reminder Notification Date')" class="text-[10px] uppercase font-bold text-slate-400 mb-1" />
                                        <x-text-input id="reminder_date" type="date" class="block w-full border-slate-200 rounded-xl h-11" wire:model="reminder_date" />
                                        <div class="mt-2 flex items-center gap-2 text-[10px] text-amber-600 font-bold bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-100">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ __('Set to 1 day before expiry by default.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Warranty Protection -->
                        <div class="space-y-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-black text-blue-500 uppercase tracking-widest flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    {{ __('Warranty Protection') }}
                                </h3>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model.live="warranty_enabled" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    <span class="ms-3 text-xs font-black text-slate-500 uppercase tracking-widest">{{ $warranty_enabled ? __('Enabled') : __('Disabled') }}</span>
                                </label>
                            </div>

                            @if($warranty_enabled)
                                <div class="bg-blue-50/50 dark:bg-blue-900/10 p-6 rounded-2xl border border-blue-100 dark:border-blue-800/30 animate-in fade-in slide-in-from-top-4 duration-500">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                        <div>
                                            <x-input-label for="warranty_duration_days" :value="__('Coverage Days')" class="text-[10px] font-bold text-blue-600/70 uppercase mb-1" />
                                            <input type="number" wire:model.live="warranty_duration_days" class="w-full border-blue-100 rounded-xl h-11 text-sm font-black focus:ring-blue-500" />
                                        </div>
                                        <div>
                                            <x-input-label for="warranty_start_mode" :value="__('Calc Start')" class="text-[10px] font-bold text-blue-600/70 uppercase mb-1" />
                                            <select wire:model.live="warranty_start_mode" class="w-full border-blue-100 rounded-xl h-11 text-xs font-bold focus:ring-blue-500">
                                                <option value="purchase_date">{{ __('Purchase Date') }}</option>
                                                <option value="activation_date">{{ __('Activation Date') }}</option>
                                                <option value="custom_date">{{ __('Custom Date') }}</option>
                                            </select>
                                        </div>
                                        <div class="flex flex-col justify-end">
                                            <div class="px-4 h-11 flex items-center bg-white dark:bg-slate-900 border border-blue-200 rounded-xl">
                                                <span class="text-[10px] font-black text-slate-400 uppercase mr-2">{{ __('Ends') }}:</span>
                                                <span class="text-sm font-black text-blue-600">{{ $warranty_end_date ? \Carbon\Carbon::parse($warranty_end_date)->format('d M Y') : '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <x-input-label for="warranty_terms_snapshot" :value="__('Custom warranty terms (For buyer)')" class="text-[10px] font-bold text-blue-600/70 uppercase mb-1" />
                                    <textarea wire:model="warranty_terms_snapshot" class="w-full border-blue-100 rounded-xl text-sm" rows="3" placeholder="{{ __('Describe coverage details for this client...') }}"></textarea>
                                </div>
                            @endif
                        </div>

                        <!-- Section 6: Internal Notes -->
                        <div class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800">
                             <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                {{ __('Internal Intelligence') }}
                             </h3>
                             <textarea id="internal_note" wire:model="internal_note" class="block w-full border-slate-200 rounded-2xl h-32 focus:ring-indigo-500 text-sm shadow-sm" placeholder="{{ __('Admin notes about this order...') }}"></textarea>
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex items-center justify-between pt-8 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('orders.index') }}" class="text-xs font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition">{{ __('Discard Changes') }}</a>
                            
                            <div class="flex gap-4">
                                <button type="submit" class="btn-phoenix px-10 h-14 text-sm tracking-wide">
                                    {{ $orderId ? __('Update Order Information') : __('Create New Order') }}
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
