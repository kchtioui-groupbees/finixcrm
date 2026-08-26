<x-app-layout>
    <div class="py-10 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            
            <!-- Welcome Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ __('System Intelligence') }}</h1>
                    <p class="text-slate-500 font-medium">{{ __('Real-time overview of your business operations') }}.</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('orders.create') }}" class="btn-phoenix">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        {{ __('New Order') }}
                    </a>
                </div>
            </div>

            <!-- KPI Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Total Paid -->
                <div class="premium-card p-6 border-l-4 border-l-emerald-500 bg-white">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                            <svg class="w-5 h-5 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Lifetime Revenue') }}</span>
                    </div>
                    <div>
                        @forelse($revenuePerCurrency as $cur => $val)
                            <div class="text-2xl font-black text-slate-900">{{ number_format($val, 2) }} <span class="text-xs text-slate-400 font-bold ml-1">{{ $cur }}</span></div>
                        @empty
                            <div class="text-2xl font-black text-slate-300">0.00</div>
                        @endforelse
                    </div>
                    <div class="mt-4 text-xs font-bold text-emerald-600 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        {{ __('Total Collected') }}
                    </div>
                </div>

                <!-- Pending Amount -->
                <div class="premium-card p-6 border-l-4 border-l-rose-500 bg-white">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-rose-50 rounded-lg text-rose-600">
                            <svg class="w-5 h-5 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Outstanding Receivables') }}</span>
                    </div>
                    <div>
                        @forelse($pendingRevenuePerCurrency as $cur => $val)
                            <div class="text-2xl font-black text-rose-500">{{ number_format($val, 2) }} <span class="text-xs text-slate-400 font-bold ml-1">{{ $cur }}</span></div>
                        @empty
                            <div class="text-2xl font-black text-slate-300">0.00</div>
                        @endforelse
                    </div>
                    <div class="mt-4 text-xs font-bold text-rose-600 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        {{ __('Action Required') }}
                    </div>
                </div>

                <!-- Client Credit -->
                <div class="premium-card p-6 border-l-4 border-l-blue-500 bg-white">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                            <svg class="w-5 h-5 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Client Wallets') }}</span>
                    </div>
                    <div>
                        @forelse($clientCreditPerCurrency as $cur => $val)
                            <div class="text-2xl font-black text-blue-600">{{ number_format($val, 2) }} <span class="text-xs text-slate-400 font-bold ml-1">{{ $cur }}</span></div>
                        @empty
                            <div class="text-2xl font-black text-slate-300">0.00</div>
                        @endforelse
                    </div>
                    <div class="mt-4 text-xs font-bold text-blue-600 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ __('Prepaid Balances') }}
                    </div>
                </div>
            </div>

            <!-- Product Status Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Active Products -->
                <div class="premium-card p-6 kpi-card-active">
                    <div class="text-[10px] font-bold uppercase tracking-widest mb-1 opacity-70">{{ __('Active Subscriptions') }}</div>
                    <div class="text-3xl font-black">{{ $activeProductsCount }}</div>
                </div>
                <!-- Expiring Soon -->
                <div class="premium-card p-6 kpi-card-warning">
                    <div class="text-[10px] font-bold uppercase tracking-widest mb-1 opacity-70">{{ __('Expiring < 30 Days') }}</div>
                    <div class="text-3xl font-black">{{ $expiringSoonCount }}</div>
                </div>
                <!-- Expired -->
                <div class="premium-card p-6 kpi-card-danger">
                    <div class="text-[10px] font-bold uppercase tracking-widest mb-1 opacity-70">{{ __('Expired Licenses') }}</div>
                    <div class="text-3xl font-black">{{ $expiredProductsCount }}</div>
                </div>
            </div>

            <!-- Due Dates KPI Grid -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-black text-slate-900">{{ __('Due Dates') }}</h2>
                    <a href="{{ route('due-dates.index') }}" class="text-xs font-black text-finix-purple uppercase tracking-widest hover:underline">{{ __('View all') }}</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach([
                        ['key' => 'today', 'label' => __('Due Today'), 'color' => 'border-l-amber-500 bg-amber-50 text-amber-600'],
                        ['key' => 'next7', 'label' => __('Next 7 Days'), 'color' => 'border-l-blue-500 bg-blue-50 text-blue-600'],
                        ['key' => 'next30', 'label' => __('Next 30 Days'), 'color' => 'border-l-indigo-500 bg-indigo-50 text-indigo-600'],
                        ['key' => 'overdue', 'label' => __('Overdue'), 'color' => 'border-l-rose-500 bg-rose-50 text-rose-600'],
                    ] as $tile)
                        <div class="premium-card p-5 border-l-4 {{ $tile['color'] }} bg-white">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ $tile['label'] }}</div>
                            <div class="text-2xl font-black text-slate-900">{{ $dueDates[$tile['key']]['count'] }} <span class="text-xs font-bold text-slate-400">{{ __('due') }}</span></div>
                            <div class="mt-1 text-sm font-bold">
                                @forelse($dueDates[$tile['key']]['amount_per_currency'] as $cur => $amount)
                                    <span>{{ number_format($amount, 2) }} {{ $cur }}</span>
                                @empty
                                    <span class="text-slate-300">0.00</span>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Upcoming Payments -->
            <div class="premium-card overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ __('Upcoming Payments') }}</h3>
                    <span class="badge-premium bg-emerald-50 text-emerald-600 border-emerald-100">{{ __('Sorted by due date') }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-500">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-3">{{ __('Due Date') }}</th>
                                <th class="px-6 py-3">{{ __('Client') }}</th>
                                <th class="px-6 py-3">{{ __('Product') }}</th>
                                <th class="px-6 py-3">{{ __('Amount') }}</th>
                                <th class="px-6 py-3">{{ __('Status') }}</th>
                                <th class="px-6 py-3">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($upcomingDueDates as $order)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-bold">{{ \Carbon\Carbon::parse($order->next_due_date)->format('d M Y') }}</td>
                                    <td class="px-6 py-4 font-medium text-slate-900">{{ $order->client->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ $order->product->name ?? __('Unknown') }}</td>
                                    <td class="px-6 py-4">{{ $order->formatAmount($order->renewal_price ?? 0) }}</td>
                                    <td class="px-6 py-4">
                                        @if($order->is_overdue_renewal)
                                            <span class="bg-rose-100 text-rose-700 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ __('Overdue') }}</span>
                                        @else
                                            <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ __('Upcoming') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <button onclick="Livewire.dispatch('open-renew-modal', { orderId: {{ $order->id }} })" class="text-xs font-black text-emerald-600 uppercase tracking-widest hover:underline">{{ __('Paid / Renew') }}</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-400 italic">{{ __('No upcoming renewals.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Actionable List -->
                <div class="lg:col-span-3 space-y-6">
                    <div class="premium-card overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ __('Actionable Reminders') }}</h3>
                            <span class="badge-premium bg-blue-50 text-blue-600 border-blue-100">{{ __('Live View') }}</span>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @forelse($reminders as $reminder)
                                <div class="p-5 flex items-center justify-between hover:bg-slate-50 transition-colors group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-finix-purple/10 group-hover:text-finix-purple transition-colors">
                                            @if($reminder['type'] === 'expiring') ⏳ @else 🔴 @endif
                                        </div>
                                         <div>
                                            <div class="font-bold text-slate-900 group-hover:text-finix-purple transition-all">{{ $reminder['client_name'] }}</div>
                                            <div class="text-xs text-slate-500 font-medium">{{ $reminder['product_name'] }} — {{ $reminder['days'] }} {{ __('days remaining') }}</div>
                                        </div>
                                    </div>
                                    <a href="{{ route('orders.edit', $reminder['order_id']) }}" class="text-xs font-bold text-finix-purple uppercase tracking-widest hover:underline">{{ __('Manage') }}</a>
                                </div>
                            @empty
                                <div class="p-10 text-center text-slate-400 font-medium italic">{{ __('No immediate actions required') }}.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Sidebar -->
                <div class="space-y-6">
                    <div class="premium-card p-6 bg-slate-900 text-white">
                        <h3 class="text-[10px] font-black uppercase tracking-widest mb-4 opacity-50">{{ __('Quick Statistics') }}</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center text-sm border-b border-white/5 pb-3">
                                <span class="opacity-60">{{ __('Total Clients') }}</span>
                                <span class="font-black">{{ $clientsCount }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm border-b border-white/5 pb-3">
                                <span class="opacity-60">{{ __('Total Orders') }}</span>
                                <span class="font-black">{{ $ordersCount }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm pb-3">
                                <span class="opacity-60">{{ __('Growth Score') }}</span>
                                <span class="font-black text-emerald-400">+12%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @livewire('due-dates.renew-modal')
</x-app-layout>
