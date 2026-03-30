<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Finix CRM — secure access to your services, payments, warranties and rewards.">
    <title>Finix CRM</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .premium-gradient {
            background: radial-gradient(circle at top center, #f8fafc 0%, #ffffff 100%);
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-900 premium-gradient min-h-screen flex flex-col">

    {{-- ─────────────────────── HEADER ─────────────────────── --}}
    <header class="fixed top-0 inset-x-0 z-50 bg-white/70 backdrop-blur-xl border-b border-slate-200/50">
        <div class="max-w-[1100px] mx-auto px-6 h-20 flex items-center justify-between">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3 group transition-opacity hover:opacity-80">
                <div class="w-10 h-10 rounded-xl bg-phoenix-gradient p-0.5 shadow-sm">
                    <div class="w-full h-full bg-white rounded-[9px] flex items-center justify-center">
                        <span class="glitter-text font-black text-xl leading-none">F</span>
                    </div>
                </div>
                <span class="font-bold text-2xl tracking-tight">Finix<span class="glitter-text">CRM</span></span>
            </a>

            {{-- Action --}}
            <div>
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('client.dashboard') }}" 
                       class="text-sm font-extrabold px-6 py-2.5 rounded-xl bg-slate-900 text-white hover:bg-slate-800 transition-all shadow-sm">
                        {{ __('Dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="text-sm font-extrabold px-6 py-2.5 rounded-xl bg-finix-purple text-white hover:opacity-90 transition-all shadow-md shadow-purple-100">
                        {{ __('Client Login') }}
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="flex-grow">
        {{-- ─────────────────────── HERO SECTION ─────────────────────── --}}
        <section class="pt-48 pb-32">
            <div class="max-w-[1100px] mx-auto px-6 text-center">
                <h1 class="text-6xl md:text-7xl font-black tracking-tight text-slate-900 mb-8 leading-[1.05]">
                    Finix CRM<br>
                    <span class="glitter-text">Client Portal</span>
                </h1>
                
                <p class="text-xl md:text-2xl text-slate-500 font-medium max-w-2xl mx-auto mb-16 leading-relaxed">
                    Manage your services, payments, and rewards in one secure place.
                </p>

                <div class="flex justify-center">
                    @auth
                        <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('client.dashboard') }}" 
                           class="inline-flex items-center gap-2 px-10 py-4.5 rounded-2xl bg-slate-900 text-white font-extrabold text-lg hover:scale-[1.02] transition-transform shadow-2xl shadow-slate-200">
                            {{ __('Access Dashboard') }}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center gap-2 px-10 py-4.5 rounded-2xl bg-slate-900 text-white font-extrabold text-lg hover:scale-[1.02] transition-transform shadow-2xl shadow-slate-200">
                            {{ __('Client Login') }}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    @endauth
                </div>
            </div>
        </section>

        {{-- ─────────────────────── TRANSPARENCY / CARDS SECTION ─────────────────────── --}}
        <section class="pb-48">
            <div class="max-w-[1100px] mx-auto px-6">
                <div class="text-center mb-20">
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-4">Transparency for Our Clients</h2>
                    <div class="h-1.5 w-20 bg-finix-purple mx-auto rounded-full"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {{-- Secure Access --}}
                    <div class="group p-10 rounded-[2rem] bg-white border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                        <div class="w-14 h-14 rounded-2xl bg-purple-50 flex items-center justify-center text-finix-purple mb-8 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-900 mb-3 uppercase tracking-tight">Secure Access</h3>
                        <p class="text-slate-500 font-medium leading-relaxed">
                            Private and encrypted access to your active digital services.
                        </p>
                    </div>

                    {{-- Payments --}}
                    <div class="group p-10 rounded-[2rem] bg-white border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 mb-8 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-900 mb-3 uppercase tracking-tight">Payments</h3>
                        <p class="text-slate-500 font-medium leading-relaxed">
                            Full visibility over your transactions and billing history.
                        </p>
                    </div>

                    {{-- Rewards --}}
                    <div class="group p-10 rounded-[2rem] bg-white border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                        <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500 mb-8 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-900 mb-3 uppercase tracking-tight">Rewards</h3>
                        <p class="text-slate-500 font-medium leading-relaxed">
                            Manage your cashback and loyalty benefits instantly.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- ─────────────────────── FOOTER (REUSED EXACTLY) ─────────────────────── --}}
    <footer class="bg-white border-t border-slate-200 py-12 relative z-10 font-sans">
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
                        <span class="font-bold text-xl tracking-tight text-slate-900">Finix<span class="bg-clip-text text-transparent bg-gradient-to-br from-orange-500 to-pink-600">CRM</span></span>
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
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest text-slate-400 font-bold uppercase tracking-widest">
                    &copy; {{ date('Y') }} Finix CRM. All rights reserved.
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
