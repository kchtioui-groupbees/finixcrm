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

        <!-- ═══════════════════ Period-filtered overview ═══════════════════ -->
        <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h2 class="text-lg font-black text-slate-900">{{ __('Period Overview') }}</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach(['today' => __('Today'), 'yesterday' => __('Yesterday'), 'week' => __('This Week'), 'month' => __('This Month'), 'year' => __('This Year'), 'custom' => __('Custom')] as $key => $label)
                        <button wire:click="setPeriod('{{ $key }}')" class="px-3 py-1.5 text-xs font-bold rounded-lg {{ $period === $key ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            @if($period === 'custom')
                <div class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-400">{{ __('From') }}</label>
                        <input type="date" wire:model.live="customFrom" class="block border-slate-200 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-400">{{ __('To') }}</label>
                        <input type="date" wire:model.live="customTo" class="block border-slate-200 rounded-lg text-sm">
                    </div>
                </div>
            @endif

            <div class="text-xs text-slate-400 font-medium">{{ __('Showing') }}: {{ $periodFrom->format('d M Y') }} → {{ $periodTo->format('d M Y') }} ({{ __('Africa/Tunis time') }})</div>

            <!-- Period stat cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div class="premium-card p-5 border-l-4 border-l-emerald-500">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Revenue (validated)') }}</div>
                    @forelse($periodStats['revenue_by_currency'] as $cur => $val)
                        <div class="text-xl font-black text-emerald-600">{{ number_format($val, 2) }} <span class="text-[10px] text-slate-400">{{ $cur }}</span></div>
                    @empty
                        <div class="text-xl font-black text-slate-300">0.00</div>
                    @endforelse
                </div>
                <div class="premium-card p-5 border-l-4 border-l-emerald-700">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Net Revenue (after refunds)') }}</div>
                    @forelse($periodStats['revenue_by_currency'] as $cur => $val)
                        <div class="text-xl font-black text-slate-900">{{ number_format($val - ($periodStats['refunds_by_currency'][$cur] ?? 0), 2) }} <span class="text-[10px] text-slate-400">{{ $cur }}</span></div>
                    @empty
                        <div class="text-xl font-black text-slate-300">0.00</div>
                    @endforelse
                </div>
                <div class="premium-card p-5 border-l-4 border-l-orange-500">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Pending Payments') }}</div>
                    <div class="text-xl font-black text-orange-600">{{ $periodStats['pending_payments']['count'] }}</div>
                    @foreach($periodStats['pending_payments']['amount_by_currency'] as $cur => $val)
                        <div class="text-[11px] text-slate-400 font-bold">{{ number_format($val, 2) }} {{ $cur }}</div>
                    @endforeach
                </div>
                <div class="premium-card p-5 border-l-4 border-l-rose-500">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Amount to Recover') }}</div>
                    @forelse($periodStats['amount_to_recover_by_currency'] as $cur => $val)
                        <div class="text-xl font-black text-rose-600">{{ number_format($val, 2) }} <span class="text-[10px] text-slate-400">{{ $cur }}</span></div>
                    @empty
                        <div class="text-xl font-black text-slate-300">0.00</div>
                    @endforelse
                </div>

                <div class="premium-card p-5">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Orders') }}</div>
                    <div class="text-xl font-black text-slate-900">{{ $periodStats['orders_total'] }}</div>
                    <div class="text-[11px] text-slate-400 font-bold">{{ __('Paid') }}: {{ $periodStats['orders_paid'] }} · {{ __('Partial') }}: {{ $periodStats['orders_partial'] }} · {{ __('Unpaid') }}: {{ $periodStats['orders_unpaid'] }}</div>
                </div>
                <div class="premium-card p-5">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('New Clients') }}</div>
                    <div class="text-xl font-black text-slate-900">{{ $periodStats['new_clients'] }}</div>
                </div>
                <div class="premium-card p-5">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Active Clients') }}</div>
                    <div class="text-xl font-black text-slate-900">{{ $periodStats['active_clients'] }}</div>
                </div>
                <div class="premium-card p-5">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Warranties') }}</div>
                    <div class="text-xl font-black text-slate-900">{{ $warrantyStats['active'] }} <span class="text-[10px] text-slate-400">{{ __('active') }}</span></div>
                    <div class="text-[11px] text-amber-600 font-bold">{{ $warrantyStats['expiring_soon'] }} {{ __('expiring in 30 days') }}</div>
                </div>

                <div class="premium-card p-5 border-l-4 border-l-finix-purple">
                    <div class="text-[10px] font-black text-finix-purple uppercase tracking-widest mb-1">{{ __('Cashback Distributed') }}</div>
                    @forelse($periodStats['cashback_distributed_by_currency'] as $cur => $val)
                        <div class="text-xl font-black text-finix-purple">{{ number_format($val, 2) }} <span class="text-[10px] text-slate-400">{{ $cur }}</span></div>
                    @empty
                        <div class="text-xl font-black text-slate-300">0.00</div>
                    @endforelse
                </div>
                <div class="premium-card p-5 border-l-4 border-l-finix-purple">
                    <div class="text-[10px] font-black text-finix-purple uppercase tracking-widest mb-1">{{ __('Cashback Used') }}</div>
                    @forelse($periodStats['cashback_used_by_currency'] as $cur => $val)
                        <div class="text-xl font-black text-finix-purple">{{ number_format($val, 2) }} <span class="text-[10px] text-slate-400">{{ $cur }}</span></div>
                    @empty
                        <div class="text-xl font-black text-slate-300">0.00</div>
                    @endforelse
                </div>
                <div class="premium-card p-5 border-l-4 border-l-blue-500">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('Total Avoirs') }}</div>
                    @forelse($periodStats['total_avoirs_by_currency'] as $cur => $val)
                        <div class="text-xl font-black text-blue-600">{{ number_format($val, 2) }} <span class="text-[10px] text-slate-400">{{ $cur }}</span></div>
                    @empty
                        <div class="text-xl font-black text-slate-300">0.00</div>
                    @endforelse
                </div>
            </div>

            <!-- Top lists -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="premium-card overflow-hidden">
                    <div class="p-4 border-b border-slate-100 bg-slate-50/30"><h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ __('Top Products') }}</h3></div>
                    <div class="divide-y divide-slate-100">
                        @forelse($periodStats['top_products'] as $p)
                            <div class="p-4 flex items-center justify-between text-sm">
                                <span class="font-bold text-slate-800">{{ $p['name'] }}</span>
                                <span class="text-slate-400 font-bold">{{ $p['orders_count'] }} {{ __('orders') }}</span>
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-400 italic text-sm">{{ __('No orders in this period') }}.</div>
                        @endforelse
                    </div>
                </div>
                <div class="premium-card overflow-hidden">
                    <div class="p-4 border-b border-slate-100 bg-slate-50/30"><h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ __('Top Payment Methods') }}</h3></div>
                    <div class="divide-y divide-slate-100">
                        @forelse($periodStats['top_payment_methods'] as $m)
                            <div class="p-4 flex items-center justify-between text-sm">
                                <span class="font-bold text-slate-800">{{ $m['label'] }}</span>
                                <span class="text-slate-400 font-bold">{{ $m['count'] }} {{ __('payments') }}</span>
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-400 italic text-sm">{{ __('No validated payments in this period') }}.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Lightweight CSS bar charts (no JS charting dependency) -->
            @php
                $maxOf = fn ($arr) => max(1, ...array_map('floatval', $arr ?: [0]));
                $revMax = $maxOf($dailySeries['revenue']);
                $ordersMax = $maxOf($dailySeries['orders']);
                $clientsMax = $maxOf($dailySeries['new_clients']);
                $unpaidMax = $maxOf($dailySeries['unpaid']);
                $cbMax = max($maxOf($dailySeries['cashback_distributed']), $maxOf($dailySeries['cashback_used']));
            @endphp
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach([
                    ['title' => __('Revenue / Day'), 'series' => $dailySeries['revenue'], 'max' => $revMax, 'color' => 'bg-emerald-500'],
                    ['title' => __('Orders / Day'), 'series' => $dailySeries['orders'], 'max' => $ordersMax, 'color' => 'bg-indigo-500'],
                    ['title' => __('New Clients / Day'), 'series' => $dailySeries['new_clients'], 'max' => $clientsMax, 'color' => 'bg-blue-500'],
                    ['title' => __('Unpaid Amount Evolution'), 'series' => $dailySeries['unpaid'], 'max' => $unpaidMax, 'color' => 'bg-rose-500'],
                ] as $chart)
                    <div class="premium-card p-5">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">{{ $chart['title'] }}</h4>
                        <div class="flex items-end gap-1 h-32">
                            @foreach($chart['series'] as $i => $val)
                                <div class="flex-1 flex flex-col items-center justify-end h-full group relative">
                                    <div class="w-full {{ $chart['color'] }} rounded-t opacity-80 group-hover:opacity-100 transition-opacity" style="height: {{ max(2, ($val / $chart['max']) * 100) }}%" title="{{ $dailySeries['labels'][$i] }}: {{ number_format($val, 2) }}"></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex justify-between mt-2 text-[9px] text-slate-400 font-bold">
                            <span>{{ $dailySeries['labels'][0] ?? '' }}</span>
                            <span>{{ $dailySeries['labels'][count($dailySeries['labels']) - 1] ?? '' }}</span>
                        </div>
                    </div>
                @endforeach

                <!-- Cashback distributed vs used -->
                <div class="premium-card p-5">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">{{ __('Cashback Distributed vs Used') }}</h4>
                    <div class="flex items-end gap-1 h-32">
                        @foreach($dailySeries['cashback_distributed'] as $i => $val)
                            <div class="flex-1 flex flex-col items-center justify-end h-full gap-0.5">
                                <div class="w-full bg-finix-purple rounded-t opacity-80" style="height: {{ max(2, ($val / $cbMax) * 45) }}%" title="{{ __('Distributed') }} {{ $dailySeries['labels'][$i] }}: {{ number_format($val, 2) }}"></div>
                                <div class="w-full bg-slate-400 rounded-t opacity-60" style="height: {{ max(2, ($dailySeries['cashback_used'][$i] / $cbMax) * 45) }}%" title="{{ __('Used') }} {{ $dailySeries['labels'][$i] }}: {{ number_format($dailySeries['cashback_used'][$i], 2) }}"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex gap-4 mt-2 text-[10px] font-bold text-slate-500">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 bg-finix-purple rounded-full"></span>{{ __('Distributed') }}</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 bg-slate-400 rounded-full"></span>{{ __('Used') }}</span>
                    </div>
                </div>

                <!-- Payment status breakdown -->
                <div class="premium-card p-5">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">{{ __('Payment Status Breakdown') }}</h4>
                    @php $totalStatus = max(1, array_sum($paymentStatusBreakdown)); @endphp
                    <div class="space-y-3">
                        @forelse(['completed' => 'bg-emerald-500', 'pending' => 'bg-amber-500', 'rejected' => 'bg-rose-500'] as $status => $color)
                            @php $count = $paymentStatusBreakdown[$status] ?? 0; @endphp
                            <div>
                                <div class="flex justify-between text-[11px] font-bold text-slate-500 mb-1"><span>{{ __($status) }}</span><span>{{ $count }}</span></div>
                                <div class="w-full bg-slate-100 rounded-full h-2"><div class="{{ $color }} h-2 rounded-full" style="width: {{ ($count / $totalStatus) * 100 }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Payment method breakdown -->
                <div class="premium-card p-5">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">{{ __('Payment Method Breakdown') }}</h4>
                    @php $methodMax = max(1, ...array_map(fn($m) => $m['count'], $periodStats['top_payment_methods']->all() ?: [['count' => 0]])); @endphp
                    <div class="space-y-3">
                        @forelse($periodStats['top_payment_methods'] as $m)
                            <div>
                                <div class="flex justify-between text-[11px] font-bold text-slate-500 mb-1"><span>{{ $m['label'] }}</span><span>{{ $m['count'] }}</span></div>
                                <div class="w-full bg-slate-100 rounded-full h-2"><div class="bg-indigo-500 h-2 rounded-full" style="width: {{ ($m['count'] / $methodMax) * 100 }}%"></div></div>
                            </div>
                        @empty
                            <div class="text-center text-slate-400 italic text-sm py-4">{{ __('No data') }}.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <!-- ═══════════════════════════════════════════════════════════════ -->

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

        <!-- Pending Payments KPI -->
        <a href="{{ route('payments.pending') }}" class="premium-card p-5 border-l-4 border-l-orange-500 bg-white flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">{{ __('Pending Payments') }}</div>
                <div class="text-2xl font-black text-slate-900">{{ $pendingPaymentsKpi['count'] }} <span class="text-xs font-bold text-slate-400">{{ __('awaiting confirmation') }}</span></div>
                <div class="mt-1 text-sm font-bold text-orange-600">
                    @forelse($pendingPaymentsKpi['amount_per_currency'] as $cur => $amount)
                        <span>{{ number_format($amount, 2) }} {{ $cur }}</span>
                    @empty
                        <span class="text-slate-300">0.00</span>
                    @endforelse
                </div>
                @if($pendingPaymentsKpi['old_count'] > 0)
                    <div class="mt-2 text-[10px] font-bold text-rose-600 uppercase tracking-widest">{{ $pendingPaymentsKpi['old_count'] }} {{ __('pending for more than 3 days') }}</div>
                @endif
            </div>
            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>

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
                    </div>
                </div>
            </div>
        </div>
    </div>

    @livewire('due-dates.renew-modal')
</div>
