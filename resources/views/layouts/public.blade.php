<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Finix CRM — digital licenses, subscriptions and services made simple.">
    <title>{{ $title ?? 'Finix CRM' }}</title>

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
            <a href="{{ route('home') }}" class="flex items-center gap-3 group transition-opacity hover:opacity-80">
                <div class="w-10 h-10 rounded-xl bg-phoenix-gradient p-0.5 shadow-sm">
                    <div class="w-full h-full bg-white rounded-[9px] flex items-center justify-center">
                        <span class="glitter-text font-black text-xl leading-none">F</span>
                    </div>
                </div>
                <span class="font-bold text-2xl tracking-tight">Finix<span class="glitter-text">CRM</span></span>
            </a>

            <nav class="hidden md:flex items-center gap-8">
                <a href="{{ route('public.payment-methods') }}" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors {{ request()->routeIs('public.payment-methods') ? 'text-slate-900' : '' }}">{{ __('Payment Methods') }}</a>
                <a href="{{ route('public.about') }}" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors {{ request()->routeIs('public.about') ? 'text-slate-900' : '' }}">{{ __('About') }}</a>
                <a href="{{ route('public.terms') }}" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors {{ request()->routeIs('public.terms') ? 'text-slate-900' : '' }}">{{ __('Terms') }}</a>
            </nav>

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

    <main class="flex-grow pt-32 pb-24">
        {{ $slot }}
    </main>

    {{-- ─────────────────────── FOOTER ─────────────────────── --}}
    <footer class="bg-white border-t border-slate-200 py-12 relative z-10 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
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
                        {{ __('Digital licenses, subscriptions and services — simple, fast and transparent.') }}
                    </p>
                </div>

                <div class="space-y-4">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-900">{{ __('Support') }}</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('public.about') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium transition">{{ __('About') }}</a></li>
                        <li><a href="{{ route('public.payment-methods') }}" class="text-sm text-slate-500 hover:text-emerald-600 font-medium transition">{{ __('Payment Methods') }}</a></li>
                        <li><a href="{{ route('public.terms') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium transition">{{ __('Terms of Use') }}</a></li>
                        <li><a href="mailto:contact@finix.tn" class="text-sm text-slate-500 hover:text-indigo-600 font-medium transition">{{ __('Contact') }}</a></li>
                        <li><a href="https://wa.me/21692871752" target="_blank" class="text-sm text-slate-500 hover:text-emerald-600 font-medium transition">{{ __('WhatsApp') }}</a></li>
                        <li><a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-finix-purple font-medium transition">{{ __('Client Login') }}</a></li>
                    </ul>
                </div>

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
                    &copy; {{ date('Y') }} Finix CRM. {{ __('All rights reserved.') }}
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
