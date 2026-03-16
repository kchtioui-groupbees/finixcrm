<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'FinixTN CRM' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#F8FAFC] text-slate-900 overflow-x-hidden">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass-nav border-b border-slate-200/50 backdrop-blur-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-phoenix-gradient p-0.5">
                        <div class="w-full h-full bg-white rounded-[6px] flex items-center justify-center">
                            <span class="glitter-text font-black text-lg">F</span>
                        </div>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-slate-900">Finix<span class="glitter-text">TN</span></span>
                </a>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('public.about') }}" class="text-sm font-semibold {{ request()->routeIs('public.about') ? 'text-finix-purple' : 'text-slate-600' }} hover:text-finix-purple transition-colors">{{ __('About') }}</a>
                    <a href="{{ route('public.payments') }}" class="text-sm font-semibold {{ request()->routeIs('public.payments') ? 'text-finix-purple' : 'text-slate-600' }} hover:text-finix-purple transition-colors">{{ __('Payments') }}</a>
                    <a href="{{ route('public.contact') }}" class="text-sm font-semibold {{ request()->routeIs('public.contact') ? 'text-finix-purple' : 'text-slate-600' }} hover:text-finix-purple transition-colors">{{ __('Contact') }}</a>
                </div>

                <div>
                    @auth
                        <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('client.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-800 transition-all shadow-xl shadow-slate-200">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-finix-purple text-white text-sm font-bold hover:bg-finix-purple-dark transition-all shadow-xl shadow-purple-200">
                            {{ __('Login') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="pt-24 min-h-screen">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="py-12 border-t border-slate-100 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded bg-slate-900 flex items-center justify-center text-[10px] font-black text-white">F</div>
                <span class="font-bold text-slate-900">FinixTN CRM</span>
            </div>
            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">&copy; {{ date('Y') }} FinixTN. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
