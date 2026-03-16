<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Client Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(!$client)
                <div class="bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 text-sm text-yellow-700 dark:text-yellow-300">
                            <p>{{ __("We couldn't find a client profile linked to your account. Please contact support.") }}</p>
                        </div>
                    </div>
                </div>
            @else
                
                <!-- Welcome Banner -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold">{{ __('Welcome back') }}, {{ $client->name }}!</h3>
                            <p class="text-gray-500 mt-1">{{ __('Here is a quick overview of your account status.') }}</p>
                        </div>
                        <div>
                        <div class="flex flex-wrap gap-3">
                             <a href="{{ route('client.payment-methods') }}" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-black py-2.5 px-6 rounded-xl transition shadow-lg shadow-slate-900/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                {{ __('Payment Methods') }}
                            </a>
                            <a href="{{ route('client.products.index') }}" class="inline-flex items-center gap-2 bg-white border-2 border-slate-100 hover:border-slate-200 text-slate-700 font-black py-2.5 px-6 rounded-xl transition">
                                {{ __('View My Products') }}
                            </a>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                     <!-- Total Active Products -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 capitalize">{{ __('Total Active Products') }}</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $kpis['active_products'] }}</div>
                    </div>
 
                    <!-- Expiring Soon -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-orange-500">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 capitalize">{{ __('Products Expiring Soon') }}</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $kpis['expiring_soon'] }}</div>
                    </div>
 
                    <!-- Expired -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-500">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 capitalize">{{ __('Expired Products') }}</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $kpis['expired'] }}</div>
                    </div>

                     <!-- Total Paid -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 capitalize">{{ __('Total Amount Paid') }}</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $client->formatAmount($kpis['total_paid']) }}</div>
                    </div>
 
                    <!-- Total Pending -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500 rounded-lg">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 capitalize">{{ __('Total Pending Amount') }}</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $client->formatAmount($kpis['total_pending']) }}</div>
                        @if($kpis['total_pending'] > 0)
                            <a href="{{ route('client.payments.index') }}" class="text-sm text-yellow-600 hover:underline mt-2 inline-block">{{ __('Review missing payments') }} →</a>
                        @endif
                    </div>

                    <!-- Rewards & Wallet -->
                    <div class="bg-gradient-to-br from-indigo-900 via-purple-900 to-emerald-800 overflow-hidden shadow-2xl sm:rounded-2xl p-6 relative group col-span-1 md:col-span-2 lg:col-span-1">
                        <!-- Decorative blur/glow -->
                        <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50 group-hover:opacity-100 transition duration-700"></div>
                        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50 group-hover:opacity-100 transition duration-700"></div>

                        <div class="relative z-10">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <div class="text-sm font-bold text-emerald-100 uppercase tracking-widest">{{ __('Rewards Wallet') }}</div>
                            </div>

                            <!-- Available balance -->
                            <div class="mt-1 text-4xl font-black text-white drop-shadow-lg">{{ $client->formatAmount($client->credit_balance) }}</div>
                            <p class="text-xs text-emerald-200/70 mt-1 font-medium uppercase tracking-wider">{{ __('Available to spend') }}</p>

                            <!-- Breakdown row -->
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div class="bg-white/10 rounded-xl p-3 backdrop-blur-sm">
                                    <div class="text-[10px] font-bold text-emerald-300 uppercase tracking-widest">{{ __('Total Earned') }}</div>
                                    <div class="text-lg font-black text-white mt-1">{{ $client->formatAmount($kpis['cashback_earned']) }}</div>
                                </div>
                                <div class="bg-white/10 rounded-xl p-3 backdrop-blur-sm">
                                    <div class="text-[10px] font-bold text-yellow-300 uppercase tracking-widest">{{ __('Pending Reward') }}</div>
                                    <div class="text-lg font-black text-white mt-1">{{ $client->formatAmount($kpis['cashback_pending']) }}</div>
                                    <div class="text-[9px] text-yellow-200/70 mt-0.5">{{ __('After full payment') }}</div>
                                </div>
                            </div>

                            <!-- Recent cashback history -->
                            @if($cashbackHistory->isNotEmpty())
                                <div class="mt-4 space-y-2">
                                    <div class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2">{{ __('Recent Rewards') }}</div>
                                    @foreach($cashbackHistory as $txn)
                                        <div class="flex items-center justify-between bg-white/5 rounded-lg px-3 py-2">
                                            <div class="text-xs text-emerald-200">
                                                {{ __('Order') }} #{{ $txn->reference_id }}
                                                <span class="text-white/40 ml-1">{{ $txn->created_at->format('d M Y') }}</span>
                                            </div>
                                            <div class="text-sm font-black text-emerald-400">+{{ $client->formatAmount($txn->amount) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-white/40 mt-4 text-center">{{ __('No cashback rewards yet. Complete a purchase to earn!') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

            @endif
        </div>
    </div>
</div>
