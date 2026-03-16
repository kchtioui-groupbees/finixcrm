<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FinixTN Client Portal</title>
    
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

                <div>
                    @auth
                        <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('client.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-800 transition-all shadow-xl shadow-slate-200">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-finix-purple text-white text-sm font-bold hover:bg-finix-purple-dark transition-all shadow-xl shadow-purple-200">
                            {{ __('Client Login') }}
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
                <span class="text-[10px] font-black uppercase tracking-widest text-finix-purple italic">{{ __('Secure Access') }}</span>
            </div>
            
            <h1 class="text-5xl lg:text-7xl font-black text-slate-900 leading-[1.1] mb-8 tracking-tight animate-fade-in-up" style="animation-delay: 100ms">
                FinixTN <br/>
                <span class="glitter-text">Client Portal</span>
            </h1>

            <p class="text-xl lg:text-2xl text-slate-700 font-bold max-w-3xl mx-auto mb-6 animate-fade-in-up" style="animation-delay: 150ms">
                A secure management portal designed to provide transparency between FinixTN and our clients.
            </p>
            
            <p class="text-lg text-slate-500 font-medium max-w-2xl mx-auto mb-12 animate-fade-in-up" style="animation-delay: 200ms">
                This platform allows FinixTN customers to securely access their purchased services, track payments, view warranties, and manage rewards in one centralized place.
            </p>

            <div class="flex items-center justify-center animate-fade-in-up" style="animation-delay: 300ms">
                <a href="{{ route('login') }}" class="px-10 py-4 rounded-2xl bg-slate-900 text-white font-bold hover:scale-105 transition-transform shadow-2xl shadow-slate-400">
                    {{ __('Client Login') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Trust / Transparency Section -->
    <section class="py-24 bg-white border-t border-slate-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-black text-slate-900 mb-6 italic">Built for Trust and Transparency</h2>
                <p class="text-lg text-slate-600 font-medium leading-relaxed max-w-3xl mx-auto">
                    The FinixTN Client Portal is designed to give our customers full visibility over their services. 
                    Clients can track their active products, payment history, warranties, and loyalty rewards in a secure environment.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex items-start gap-4 p-6 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-finix-purple flex-shrink-0">🔒</div>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-1">Secure access to purchased services</h4>
                        <p class="text-sm text-slate-500 font-medium">Restricted access ensuring only you can manage your digital assets.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-6 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">📊</div>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-1">Payment and billing transparency</h4>
                        <p class="text-sm text-slate-500 font-medium">Clear history of all transactions and pending balances.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-6 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">⏳</div>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-1">Warranty and expiration tracking</h4>
                        <p class="text-sm text-slate-500 font-medium">Never miss a renewal with automated expiration alerts.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-6 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">⭐</div>
                    <div>
                        <h4 class="font-bold text-slate-900 mb-1">Rewards and cashback management</h4>
                        <p class="text-sm text-slate-500 font-medium">Track and apply your loyalty rewards directly to new services.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer - Reused exactly as in app.blade.php -->
    <footer class="bg-white border-t border-slate-200 py-12 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Brand -->
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
                
                <!-- Quick Links -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-900">{{ __('Support') }}</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('client.about') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium transition italic">{{ __('About Us') }}</a></li>
                        <li><a href="{{ route('client.contact') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium transition italic">{{ __('Contact Support') }}</a></li>
                        <li><a href="{{ route('client.payment-methods') }}" class="text-sm text-slate-500 hover:text-emerald-600 font-medium transition italic">{{ __('Payment Methods') }}</a></li>
                    </ul>
                </div>

                <!-- Platforms -->
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

                <!-- Social -->
                <div class="space-y-4 flex flex-col items-start md:items-end">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-900">{{ __('Connect') }}</h4>
                    <div class="flex gap-4">
                        <a href="https://www.facebook.com/profile.php?id=61586967473792" target="_blank" class="p-2.5 bg-slate-50 rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition border border-transparent hover:border-blue-100">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://www.instagram.com/finix_tn_/" target="_blank" class="p-2.5 bg-slate-50 rounded-xl text-slate-400 hover:text-pink-600 hover:bg-pink-50 transition border border-transparent hover:border-pink-100">
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
