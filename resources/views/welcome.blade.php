<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="FinixTN Client Portal — secure access to your services, payments, warranties and rewards.">
    <title>FinixTN — Client Portal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#F8FAFC] text-slate-900">

    {{-- ─────────────────────── NAVBAR ─────────────────────── --}}
    <nav class="fixed top-0 inset-x-0 z-50 backdrop-blur-md bg-white/80 border-b border-slate-200/70">
        <div class="max-w-5xl mx-auto px-6 h-16 flex items-center justify-between">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <div class="w-8 h-8 rounded-lg bg-phoenix-gradient p-0.5 shadow shadow-purple-200 group-hover:rotate-6 transition-transform duration-300">
                    <div class="w-full h-full bg-white rounded-[6px] flex items-center justify-center">
                        <span class="glitter-text font-black text-base leading-none">F</span>
                    </div>
                </div>
                <span class="font-bold text-lg tracking-tight">Finix<span class="glitter-text">TN</span></span>
            </a>

            {{-- CTA --}}
            @auth
                <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('client.dashboard') }}"
                   class="text-sm font-bold px-5 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-700 transition-colors">
                    {{ __('Dashboard') }} →
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="text-sm font-bold px-5 py-2 rounded-xl bg-finix-purple text-white hover:opacity-90 transition-opacity shadow-md shadow-purple-100">
                    {{ __('Client Login') }}
                </a>
            @endauth
        </div>
    </nav>

    {{-- ─────────────────────── HERO ─────────────────────── --}}
    <main>
        <section class="pt-40 pb-28">
            {{-- Subtle ambient glow --}}
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-finix-purple/5 blur-[100px] pointer-events-none"></div>

            <div class="relative max-w-3xl mx-auto px-6 text-center">

                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 px-3 py-1 mb-10 rounded-full border border-purple-100 bg-purple-50">
                    <span class="w-1.5 h-1.5 rounded-full bg-finix-purple animate-pulse"></span>
                    <span class="text-[11px] font-black uppercase tracking-widest text-finix-purple">Secure Portal</span>
                </div>

                {{-- Title --}}
                <h1 class="text-5xl sm:text-6xl font-black tracking-tight leading-[1.08] mb-8">
                    FinixTN<br><span class="glitter-text">Client Portal</span>
                </h1>

                {{-- Subtitle --}}
                <p class="text-lg text-slate-500 font-medium leading-relaxed mb-12 max-w-xl mx-auto">
                    A secure portal for FinixTN customers to manage services, payments, warranties and rewards.
                </p>

                {{-- Primary action --}}
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('client.dashboard') }}"
                       class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-slate-900 text-white font-bold text-sm hover:scale-105 transition-transform shadow-xl shadow-slate-300">
                        Go to Dashboard →
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-slate-900 text-white font-bold text-sm hover:scale-105 transition-transform shadow-xl shadow-slate-300">
                        Client Login →
                    </a>
                @endauth
            </div>
        </section>

        {{-- ─────────────────────── CARDS ─────────────────────── --}}
        <section class="pb-32">
            <div class="max-w-5xl mx-auto px-6">

                {{-- Section label --}}
                <div class="text-center mb-14">
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-3">What you can do</p>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900">Transparency for Our Clients</h2>
                </div>

                {{-- 3-card grid --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- Card 1 --}}
                    <div class="group p-8 rounded-3xl bg-white border border-slate-100 hover:border-purple-200 hover:shadow-lg hover:shadow-purple-50 transition-all duration-300">
                        <div class="w-11 h-11 rounded-2xl bg-purple-50 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-finix-purple" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mb-2">Secure Client Access</h3>
                        <p class="text-sm text-slate-500 font-medium leading-relaxed">
                            Private, credential-protected access to all your active digital services.
                        </p>
                    </div>

                    {{-- Card 2 --}}
                    <div class="group p-8 rounded-3xl bg-white border border-slate-100 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-50 transition-all duration-300">
                        <div class="w-11 h-11 rounded-2xl bg-emerald-50 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mb-2">Payment Transparency</h3>
                        <p class="text-sm text-slate-500 font-medium leading-relaxed">
                            Full history of transactions, outstanding amounts and payment allocations.
                        </p>
                    </div>

                    {{-- Card 3 --}}
                    <div class="group p-8 rounded-3xl bg-white border border-slate-100 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-50 transition-all duration-300">
                        <div class="w-11 h-11 rounded-2xl bg-amber-50 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mb-2">Warranty &amp; Rewards</h3>
                        <p class="text-sm text-slate-500 font-medium leading-relaxed">
                            Track expiration dates, file warranty claims, and manage cashback rewards.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- ─────────────────────── FOOTER (exact copy from app.blade.php) ─────────────────────── --}}
    <footer class="bg-white border-t border-slate-200 py-12 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                {{-- Brand --}}
                <div class="col-span-1 md:col-span-2 space-y-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-400 via-pink-500 to-purple-600 p-0.5 group-hover:rotate-12 transition-transform duration-300 shadow-lg shadow-pink-500/20">
                            <div class="w-full h-full bg-white rounded-[6px] flex items-center justify-center">
                                <span class="bg-clip-text text-transparent bg-gradient-to-br from-orange-400 to-pink-500 font-black text-lg">F</span>
                            </div>
                        </div>
                        <span class="font-bold text-xl tracking-tight text-slate-900">Finix<span class="bg-clip-text text-transparent bg-gradient-to-br from-orange-500 to-pink-600">TN</span></span>
                    </a>
                    <p class="text-slate-500 text-sm font-medium leading-relaxed max-w-xs">
                        {{ __('Footer Tagline') }}
                    </p>
                </div>

                {{-- Quick Links --}}
                <div class="space-y-4">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-900">{{ __('Support') }}</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('client.about') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium transition italic">{{ __('About Us') }}</a></li>
                        <li><a href="{{ route('client.contact') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium transition italic">{{ __('Contact Support') }}</a></li>
                        <li><a href="{{ route('client.payment-methods') }}" class="text-sm text-slate-500 hover:text-emerald-600 font-medium transition italic">{{ __('Payment Methods') }}</a></li>
                    </ul>
                </div>

                {{-- Platforms --}}
                <div class="space-y-4">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-900">{{ __('Our Platforms') }}</h4>
                    <ul class="space-y-2">
                        <li>
                            <a href="https://finix.tn" target="_blank" class="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-900 font-medium transition group">
                                <span class="text-slate-300 group-hover:text-amber-500">🌐</span>
                                Finix.tn
                            </a>
                        </li>
                        <li>
                            <a href="https://finixtools.com" target="_blank" class="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-900 font-medium transition group">
                                <span class="text-slate-300 group-hover:text-indigo-500">🛠️</span>
                                Finixtools.com
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Social --}}
                <div class="space-y-4 flex flex-col items-start md:items-end">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-900">{{ __('Connect') }}</h4>
                    <div class="flex gap-4">
                        <a href="https://www.facebook.com/profile.php?id=61586967473792" target="_blank"
                           class="p-2.5 bg-slate-50 rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition border border-transparent hover:border-blue-100">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://www.instagram.com/finix_tn_/" target="_blank"
                           class="p-2.5 bg-slate-50 rounded-xl text-slate-400 hover:text-pink-600 hover:bg-pink-50 transition border border-transparent hover:border-pink-100">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">
                    &copy; {{ date('Y') }} FinixTN CRM. All rights reserved.
                </p>
                <div class="flex gap-6">
                    <span class="text-[10px] text-slate-300 font-black uppercase tracking-tighter">Premium SaaS Solution</span>
                    <span class="text-[10px] text-slate-300 font-black uppercase tracking-tighter">Tunisia</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
