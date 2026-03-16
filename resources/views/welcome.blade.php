<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FinixTN CRM - Premium SaaS Solution</title>
    
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
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-phoenix-gradient p-0.5 shadow-lg shadow-purple-500/20">
                        <div class="w-full h-full bg-white rounded-[6px] flex items-center justify-center">
                            <span class="glitter-text font-black text-lg">F</span>
                        </div>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-slate-900">Finix<span class="glitter-text">TN</span></span>
                </div>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('public.about') }}" class="text-sm font-semibold text-slate-600 hover:text-finix-purple transition-colors">{{ __('About') }}</a>
                    <a href="{{ route('public.payments') }}" class="text-sm font-semibold text-slate-600 hover:text-finix-purple transition-colors">{{ __('Payments') }}</a>
                    <a href="{{ route('public.contact') }}" class="text-sm font-semibold text-slate-600 hover:text-finix-purple transition-colors">{{ __('Contact') }}</a>
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

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1200px] h-[600px] bg-finix-purple/5 blur-[120px] pointer-events-none"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-50 border border-purple-100 mb-8 animate-fade-in-up">
                <span class="w-2 h-2 rounded-full bg-finix-purple animate-pulse"></span>
                <span class="text-[10px] font-black uppercase tracking-widest text-finix-purple italic">{{ __('New Release') }}</span>
            </div>
            
            <h1 class="text-5xl lg:text-7xl font-black text-slate-900 leading-[1.1] mb-8 tracking-tight animate-fade-in-up" style="animation-delay: 100ms">
                Elevate Your Business <br/>
                <span class="glitter-text">Management Experience</span>
            </h1>
            
            <p class="text-lg lg:text-xl text-slate-500 font-medium max-w-2xl mx-auto mb-12 animate-fade-in-up" style="animation-delay: 200ms">
                A robust, auditable, and automated CRM platform built for the modern era. Manage clients, orders, and loyalty with ease.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 animate-fade-in-up" style="animation-delay: 300ms">
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-slate-900 text-white font-bold hover:scale-105 transition-transform shadow-2xl shadow-slate-400">
                    {{ __('Get Started Now') }}
                </a>
                <a href="{{ route('public.about') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white text-slate-900 font-bold border border-slate-200 hover:bg-slate-50 transition-colors">
                    {{ __('Learn More') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-purple-200 transition-colors group">
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 flex items-center justify-center text-finix-purple text-2xl mb-6 group-hover:scale-110 transition-transform">💎</div>
                    <h3 class="text-xl font-bold text-slate-900 mb-4 italic">Automated Billing</h3>
                    <p class="text-slate-500 font-medium leading-relaxed">Smart payment allocations and automatic reminders to keep your cash flow healthy.</p>
                </div>

                <!-- Feature 2 -->
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-emerald-200 transition-colors group">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-600 text-2xl mb-6 group-hover:scale-110 transition-transform">🔒</div>
                    <h3 class="text-xl font-bold text-slate-900 mb-4 italic">Secure Portal</h3>
                    <p class="text-slate-500 font-medium leading-relaxed">Dedicated client portal for managing subscriptions, rewards, and support tickets.</p>
                </div>

                <!-- Feature 3 -->
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-amber-200 transition-colors group">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-600 text-2xl mb-6 group-hover:scale-110 transition-transform">🚀</div>
                    <h3 class="text-xl font-bold text-slate-900 mb-4 italic">Cashback System</h3>
                    <p class="text-slate-500 font-medium leading-relaxed">Reward loyalty with our automated cashback engine. Build trust and recurring value.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer (Simplified) -->
    <footer class="py-12 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded bg-slate-900 flex items-center justify-center text-[10px] font-black text-white">F</div>
                <span class="font-bold text-slate-900">FinixTN CRM</span>
            </div>
            <div class="flex gap-8 text-sm font-bold text-slate-400">
                <a href="{{ route('public.about') }}" class="hover:text-finix-purple">{{ __('About') }}</a>
                <a href="{{ route('public.payments') }}" class="hover:text-finix-purple">{{ __('Payments') }}</a>
                <a href="{{ route('public.contact') }}" class="hover:text-finix-purple">{{ __('Contact') }}</a>
            </div>
            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">&copy; {{ date('Y') }} FinixTN. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
