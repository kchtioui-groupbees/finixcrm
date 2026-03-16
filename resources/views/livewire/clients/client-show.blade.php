    <div class="py-10 relative">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('clients.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-lg transition text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        </a>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('Relationship Profile') }}</span>
                    </div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight">{{ $client->name }}</h1>
                    <div class="flex items-center gap-4 mt-2">
                        <span class="flex items-center gap-1.5 text-sm font-medium text-slate-500">
                            <svg class="w-4 h-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            {{ $client->email }}
                        </span>
                        <span class="badge-premium bg-finix-purple/10 text-finix-purple border-finix-purple/20">{{ $client->currency }} {{ __('Account') }}</span>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('clients.edit', $client) }}" class="btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        {{ __('Modify Records') }}
                    </a>
                </div>
            </div>

            <!-- Client Financial Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <div class="premium-card p-6 border-t-4 border-t-emerald-500">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Settled Revenue') }}</div>
                    <div class="text-2xl font-black text-slate-900">{{ $client->formatAmount($client->orders->sum('paid_amount')) }}</div>
                </div>
                <div class="premium-card p-6 border-t-4 border-t-rose-500">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Unpaid Dues') }}</div>
                    <div class="text-2xl font-black text-rose-600">{{ $client->formatAmount($client->orders->sum('pending_amount')) }}</div>
                </div>
                <div class="premium-card p-6 border-t-4 border-t-blue-500">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Prepaid Credit') }}</div>
                    <div class="text-2xl font-black text-blue-600">{{ $client->formatAmount($client->credit_balance) }}</div>
                </div>
                <div class="premium-card p-6 border-t-4 border-t-finix-purple">
                    <div class="flex items-center gap-1 mb-1">
                        <svg class="w-3 h-3 text-finix-purple" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <div class="text-[10px] font-black text-finix-purple uppercase tracking-widest">{{ __('Rewards') }}</div>
                    </div>
                    <div class="text-2xl font-black text-slate-900">{{ $client->formatAmount(\App\Models\ClientBalanceTransaction::where('client_id', $client->id)->where('type', 'cashback_reward')->sum('amount')) }}</div>
                </div>
                <div class="premium-card p-6">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Engagement') }}</div>
                    <div class="text-2xl font-black text-slate-900">{{ $client->orders->count() }} <span class="text-xs font-bold text-slate-400 ml-1">{{ __('Orders') }}</span></div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <div class="lg:col-span-3 space-y-6">
                    <div class="premium-card overflow-hidden">
                        <!-- Navigation Tabs -->
                        <div class="flex border-b border-slate-100 px-4 bg-slate-50/30 overflow-x-auto scrollbar-hide">
                            <button wire:click="setTab('overview')" class="px-6 py-4 text-[10px] font-black uppercase tracking-widest transition-colors {{ $activeTab === 'overview' ? 'text-finix-purple border-b-2 border-finix-purple' : 'text-slate-500 hover:text-slate-900' }}">{{ __('Overview') }}</button>
                            <button wire:click="setTab('orders')" class="px-6 py-4 text-[10px] font-black uppercase tracking-widest transition-colors {{ $activeTab === 'orders' ? 'text-finix-purple border-b-2 border-finix-purple' : 'text-slate-500 hover:text-slate-900' }}">{{ __('Portfolio') }}</button>
                            <button wire:click="setTab('transactions')" class="px-6 py-4 text-[10px] font-black uppercase tracking-widest transition-colors {{ $activeTab === 'transactions' ? 'text-finix-purple border-b-2 border-finix-purple' : 'text-slate-500 hover:text-slate-900' }}">{{ __('Ledger') }}</button>
                            <button wire:click="setTab('balance')" class="px-6 py-4 text-[10px] font-black uppercase tracking-widest transition-colors {{ $activeTab === 'balance' ? 'text-finix-purple border-b-2 border-finix-purple' : 'text-slate-500 hover:text-slate-900' }}">{{ __('Credit') }}</button>
                            <button wire:click="setTab('warranty')" class="px-6 py-4 text-[10px] font-black uppercase tracking-widest transition-colors {{ $activeTab === 'warranty' ? 'text-finix-purple border-b-2 border-finix-purple' : 'text-slate-500 hover:text-slate-900' }}">{{ __('Support') }}</button>
                            <button wire:click="setTab('notes')" class="px-6 py-4 text-[10px] font-black uppercase tracking-widest transition-colors {{ $activeTab === 'notes' ? 'text-finix-purple border-b-2 border-finix-purple' : 'text-slate-500 hover:text-slate-900' }}">{{ __('Notes') }}</button>
                        </div>

                        <div class="p-8">
                            @if($activeTab === 'overview')
                                <div class="space-y-8">
                                    <div class="grid grid-cols-2 gap-8">
                                        <div>
                                            <h4 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-3">{{ __('Contact Information') }}</h4>
                                            <div class="space-y-3">
                                                <div class="flex items-center gap-3 text-sm">
                                                    <span class="text-gray-500 w-12 text-[10px] uppercase font-bold">{{ __('Email') }}</span>
                                                    <span class="text-white font-medium">{{ $client->email }}</span>
                                                </div>
                                                <div class="flex items-center gap-3 text-sm">
                                                    <span class="text-gray-500 w-12 text-[10px] uppercase font-bold">{{ __('Phone') }}</span>
                                                    <span class="text-white font-medium">{{ $client->phone ?? __('Not provided') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-3">{{ __('Client Status') }}</h4>
                                            <div class="flex flex-wrap gap-2">
                                                <span class="badge-premium bg-emerald-500/10 text-emerald-500 border-emerald-500/20">{{ __('Verified Email') }}</span>
                                                <span class="badge-premium bg-blue-500/10 text-blue-500 border-blue-500/20">{{ __('Active Subscription') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">{{ __('Recent Portfolio Activity') }}</h4>
                                        <div class="space-y-4">
                                            @forelse($client->orders()->latest()->take(3)->get() as $order)
                                                <div class="flex items-center justify-between p-4 bg-white/5 border border-white/5 rounded-xl hover:bg-white/10 transition group">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-10 h-10 rounded-lg bg-phoenix-gradient p-0.5">
                                                            <div class="w-full h-full bg-[#121212] rounded-[7px] flex items-center justify-center text-xs font-black">
                                                                📦
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-white group-hover:text-finix-orange transition-colors">{{ $order->product->name }}</div>
                                                            <div class="text-[10px] text-gray-500 uppercase font-bold">{{ __('Purchased') }} {{ $order->created_at->format('M d, Y') }}</div>
                                                        </div>
                                                    </div>
                                                    <span class="text-xs font-black text-gray-400 uppercase tracking-tighter">{{ $order->created_at->diffForHumans() }}</span>
                                                </div>
                                            @empty
                                                <div class="text-center py-8 text-gray-500 italic text-sm">{{ __('No recent activity') }}.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                            @elseif($activeTab === 'orders')
                                <div class="overflow-x-auto rounded-xl border border-white/5">
                                    <table class="w-full text-sm text-left">
                                        <thead class="text-xs uppercase bg-white/5 font-black tracking-widest text-gray-400">
                                            <tr>
                                                <th class="px-6 py-4">{{ __('Product') }}</th>
                                                <th class="px-6 py-4 text-right">{{ __('Financials') }}</th>
                                                <th class="px-6 py-4">{{ __('Support Status') }}</th>
                                                <th class="px-6 py-4 text-center">{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/5">
                                            @foreach($orders as $order)
                                                <tr class="hover:bg-white/[0.02] transition-colors cursor-pointer" onclick="window.location='{{ route('orders.edit', $order) }}'">
                                                    <td class="px-6 py-4">
                                                        <div class="font-bold text-white">{{ $order->product->name }}</div>
                                                        <div class="text-[10px] text-gray-500 font-mono italic">{{ $order->purchase_date->format('Y-m-d') }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 text-right">
                                                        <div class="font-black text-white">{{ $client->formatAmount($order->price) }}</div>
                                                        @if($order->pending_amount > 0)
                                                            <div class="text-[10px] text-rose-500 font-bold">{{ __('Pending') }}: {{ $client->formatAmount($order->pending_amount) }}</div>
                                                        @else
                                                            <div class="text-[10px] text-emerald-500 font-bold uppercase">{{ __('Fully Paid') }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($order->warranty_enabled)
                                                            <div class="text-[10px] text-indigo-400 font-black uppercase">Warranty v1.0</div>
                                                            <div class="text-xs text-indigo-500/80">{{ __('Ends') }} {{ $order->warranty_end_date->format('Y-m-d') }}</div>
                                                        @else
                                                            <span class="text-xs text-gray-600">{{ __('No active warranty') }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-center">
                                                        <span class="badge-premium {{ $order->status === 'active' ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-gray-500/10 text-gray-500 border-gray-500/20' }}">
                                                            {{ $order->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="mt-4">{{ $orders->links() }}</div>
                                </div>

                            @elseif($activeTab === 'transactions')
                                <div class="overflow-x-auto rounded-xl border border-white/5">
                                    <table class="w-full text-sm text-left">
                                        <thead class="text-xs uppercase bg-white/5 font-black tracking-widest text-gray-400">
                                            <tr>
                                                <th class="px-6 py-4">{{ __('Date') }}</th>
                                                <th class="px-6 py-4">{{ __('Payment Method') }}</th>
                                                <th class="px-6 py-4 text-right">{{ __('Total Amount') }}</th>
                                                <th class="px-6 py-4 text-center">{{ __('Execution') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/5">
                                            @foreach($payments as $payment)
                                                <tr class="hover:bg-white/[0.02]">
                                                    <td class="px-6 py-4 text-xs text-gray-300">{{ $payment->payment_date->format('M d, Y') }}</td>
                                                    <td class="px-6 py-4 uppercase text-[10px] font-black tracking-wider text-white">
                                                        <span class="p-1.5 bg-white/5 rounded-md border border-white/10">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                                                    </td>
                                                    <td class="px-6 py-4 text-right font-black text-white text-lg">{{ $client->formatAmount($payment->amount) }}</td>
                                                    <td class="px-6 py-4 text-center">
                                                        <span class="badge-premium {{ $payment->status === 'completed' ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-amber-500/10 text-amber-500 border-amber-500/20' }}">
                                                            {{ __($payment->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="p-4">{{ $payments->links() }}</div>
                                </div>

                            @elseif($activeTab === 'balance')
                                <div class="overflow-x-auto rounded-xl border border-white/5">
                                    <table class="w-full text-sm text-left">
                                        <thead class="text-xs uppercase bg-white/5 font-black tracking-widest text-gray-400">
                                            <tr>
                                                <th class="px-6 py-4">{{ __('Timestamp') }}</th>
                                                <th class="px-6 py-4">{{ __('Movement Type') }}</th>
                                                <th class="px-6 py-4 text-right">{{ __('Flow') }}</th>
                                                <th class="px-6 py-4">{{ __('Description') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/5">
                                            @foreach($transactions as $tx)
                                                <tr class="hover:bg-white/[0.02]">
                                                    <td class="px-6 py-4 text-xs text-gray-500 font-mono">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                                                    <td class="px-6 py-4">
                                                        @if($tx->type === 'cashback_reward')
                                                            <span class="text-[10px] font-black uppercase text-indigo-400 bg-indigo-500/10 px-2.5 py-1.5 rounded-lg border border-indigo-500/20 flex items-center gap-1.5 w-fit shadow-glow-sm">
                                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                                {{ __('REWARD') }}
                                                            </span>
                                                        @else
                                                            <span class="text-[10px] font-black uppercase {{ $tx->amount > 0 ? 'text-emerald-500 bg-emerald-500/10' : 'text-rose-500 bg-rose-500/10' }} px-2 py-1 rounded-md">
                                                                {{ str_replace('_', ' ', $tx->type) }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-right font-black {{ $tx->amount > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                                        {{ $tx->amount > 0 ? '+' : '' }}{{ $client->formatAmount(abs($tx->amount)) }}
                                                    </td>
                                                    <td class="px-6 py-4 text-xs text-gray-400 italic">{{ $tx->description }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="p-4">{{ $transactions->links() }}</div>
                                </div>

                            @elseif($activeTab === 'warranty')
                                <div class="space-y-8">
                                    <!-- Active Warranties -->
                                    <div>
                                        <h4 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-4 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                            {{ __('Active System Warranties') }}
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @forelse($activeWarranties as $wOrder)
                                                <div class="premium-card p-5 relative overflow-hidden group">
                                                    <div class="absolute right-0 top-0 w-16 h-16 bg-phoenix-gradient opacity-10 blur-xl group-hover:opacity-20 transition-opacity"></div>
                                                    <div class="flex justify-between items-start mb-4">
                                                        <div>
                                                            <div class="font-black text-white text-lg">{{ $wOrder->product->name }}</div>
                                                            <div class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">{{ __('Valid until') }} {{ $wOrder->warranty_end_date->format('M d, Y') }}</div>
                                                        </div>
                                                        <span class="p-2 bg-emerald-500/10 text-emerald-500 rounded-lg text-xs font-black">
                                                            {{ now()->diffInDays($wOrder->warranty_end_date) }}d {{ __('LEFT') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-span-full py-12 text-center text-gray-500 border border-white/5 border-dashed rounded-2xl italic">{{ __('No active manufacturer warranties found') }}.</div>
                                            @endforelse
                                        </div>
                                    </div>

                                    <!-- Claims History -->
                                    <div>
                                        <h4 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-4">{{ __('Service & Claim History') }}</h4>
                                        <div class="overflow-x-auto rounded-xl border border-white/5">
                                            <table class="w-full text-sm text-left">
                                                <thead class="text-xs uppercase bg-white/5 font-black tracking-widest text-gray-400">
                                                    <tr>
                                                        <th class="px-6 py-4">{{ __('Claimed Product') }}</th>
                                                        <th class="px-6 py-4">{{ __('Submission Date') }}</th>
                                                        <th class="px-6 py-4 text-center">{{ __('Ticket Status') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-white/5">
                                                    @forelse($claims as $claim)
                                                        <tr>
                                                            <td class="px-6 py-4 font-bold text-white">{{ $claim->order->product->name }}</td>
                                                            <td class="px-6 py-4 text-xs text-gray-400 font-mono italic">{{ $claim->created_at->format('Y-m-d') }}</td>
                                                            <td class="px-6 py-4 text-center">
                                                                <span class="badge-premium bg-amber-500/10 text-amber-500 border-amber-500/20">
                                                                    {{ __($claim->status) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="px-6 py-12 text-center text-gray-500 italic">{{ __('No historical claims recorded') }}.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            @elseif($activeTab === 'notes')
                                <div class="space-y-6">
                                    <div class="premium-card p-6 bg-white/[0.02]">
                                        <h4 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">{{ __('Update Internal Records') }}</h4>
                                        <textarea wire:model="newNote" rows="4" class="input-premium resize-none mb-4" placeholder="{{ __('Type confidential client notes here...') }}"></textarea>
                                        <div class="flex justify-end">
                                            <button wire:click="addNote" class="btn-phoenix">{{ __('Commit Note Changes') }}</button>
                                        </div>
                                    </div>
                                    <div class="p-6 bg-[#050505] border border-white/5 rounded-2xl font-mono text-sm leading-relaxed text-gray-400">
                                        <div class="flex items-center gap-2 mb-4">
                                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                                            <span class="text-[10px] font-black uppercase text-gray-500">{{ __('Live Transcript') }}</span>
                                        </div>
                                        {{ $client->notes ?: __('No records found in database') . '.' }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar (Actions & Info) -->
                <div class="space-y-8">
                    <div class="premium-card overflow-hidden">
                        <div class="p-6 border-b border-white/5 bg-white/[0.02]">
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-500 flex items-center gap-2">
                                <span class="w-1 h-3 bg-phoenix-gradient rounded-full"></span>
                                {{ __('Administrative Actions') }}
                            </h3>
                        </div>
                        <div class="p-4 space-y-2">
                            <a href="{{ route('orders.create', ['client_id' => $client->id]) }}" class="flex items-center gap-3 p-4 rounded-xl hover:bg-white/5 transition-all group">
                                <span class="p-2 bg-emerald-500/10 text-emerald-500 rounded-lg group-hover:bg-emerald-500 group-hover:text-white transition-all shadow-glow">
                                    <svg class="w-5 h-5 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                </span>
                                <span class="text-sm font-bold text-gray-200">{{ __('Generate New Order') }}</span>
                            </a>
                            <a href="{{ route('payments.create', ['client_id' => $client->id]) }}" class="flex items-center gap-3 p-4 rounded-xl hover:bg-white/5 transition-all group">
                                <span class="p-2 bg-blue-500/10 text-blue-500 rounded-lg group-hover:bg-blue-500 group-hover:text-white transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </span>
                                <span class="text-sm font-bold text-gray-200">{{ __('Initialize Payment') }}</span>
                            </a>
                            <a href="{{ route('claims.index') }}" class="flex items-center gap-3 p-4 rounded-xl hover:bg-white/5 transition-all group">
                                <span class="p-2 bg-rose-500/10 text-rose-500 rounded-lg group-hover:bg-rose-500 group-hover:text-white transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                </span>
                                <span class="text-sm font-bold text-gray-200">{{ __('Open Service Claim') }}</span>
                            </a>
                        </div>
                    </div>

                    <div class="premium-card p-8 group">
                        <h4 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-6 flex items-center justify-between">
                            {{ __('Internal Intelligence') }}
                            <svg class="w-3 h-3 text-emerald-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                        </h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center text-sm border-b border-white/5 pb-4">
                                <span class="text-gray-500 font-medium">{{ __('Lifetime Revenue') }}</span>
                                <span class="font-black text-white italic">{{ $client->formatAmount($client->payments()->where('status', 'completed')->sum('amount')) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm border-b border-white/5 pb-4 text-xs">
                                <span class="text-gray-500">{{ __('Last Encounter') }}</span>
                                <span class="font-bold text-gray-300">{{ $client->payments()->latest()->first()?->created_at?->diffForHumans() ?: 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-500 italic">{{ __('Member since') }} {{ $client->created_at->format('M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
