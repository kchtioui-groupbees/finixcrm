<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Finix CRM</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-[#F8FAFC] text-slate-900">
        <div class="min-h-screen flex flex-col relative overflow-hidden">
            <!-- Ambient Background Glow (Subtle for light mode) -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-finix-purple/5 blur-[120px] animate-soft-glow pointer-events-none"></div>

            <nav class="glass-nav border-b border-slate-200/80">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                                    <div class="w-8 h-8 rounded-lg bg-phoenix-gradient p-0.5 group-hover:rotate-12 transition-transform duration-300">
                                        <div class="w-full h-full bg-white rounded-[6px] flex items-center justify-center">
                                            <span class="glitter-text font-black text-lg">F</span>
                                        </div>
                                    </div>
                                    <span class="font-bold text-xl tracking-tight text-slate-900">Finix<span class="glitter-text">CRM</span></span>
                                </a>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                @if(Auth::user()->isAdmin())
                                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 transition-colors">
                                        {{ __('Dashboard') }}
                                    </x-nav-link>
                                    <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 transition-colors">
                                        {{ __('Clients') }}
                                    </x-nav-link>
                                    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 transition-colors">
                                        {{ __('Products') }}
                                    </x-nav-link>
                                    <x-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 transition-colors">
                                        {{ __('Orders') }}
                                    </x-nav-link>
                                    <x-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 transition-colors">
                                        {{ __('Payments') }}
                                    </x-nav-link>
                                    <x-nav-link :href="route('claims.index')" :active="request()->routeIs('claims.*')" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 transition-colors">
                                        {{ __('Warranty Claims') }}
                                    </x-nav-link>
                                @else
                                    <x-nav-link :href="route('client.dashboard')" :active="request()->routeIs('client.dashboard')" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 transition-colors">
                                        {{ __('Dashboard') }}
                                    </x-nav-link>
                                    <x-nav-link :href="route('client.products.index')" :active="request()->routeIs('client.products.*')" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 transition-colors">
                                        {{ __('My Products') }}
                                    </x-nav-link>
                                    <x-nav-link :href="route('client.payments.index')" :active="request()->routeIs('client.payments.*')" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 transition-colors">
                                        {{ __('My Payments') }}
                                    </x-nav-link>
                                    <x-nav-link :href="route('client.contact')" :active="request()->routeIs('client.contact')" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 transition-colors">
                                        {{ __('Support') }}
                                    </x-nav-link>
                                @endif
                            </div>
                        </div>

                        <!-- Multi-language Switcher -->
                        <div class="hidden sm:flex sm:items-center sm:ml-4">
                            <x-dropdown align="{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 border border-slate-200 text-sm leading-4 font-black rounded-xl text-slate-700 bg-white hover:bg-slate-50 focus:outline-none transition ease-in-out duration-150 shadow-sm gap-2">
                                        <span>
                                            @if(app()->getLocale() == 'en') 🇺🇸 English
                                            @elseif(app()->getLocale() == 'fr') 🇫🇷 Français
                                            @elseif(app()->getLocale() == 'ar') 🇹🇳 العربية
                                            @endif
                                        </span>
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('lang.switch', 'en')">
                                        🇺🇸 English
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('lang.switch', 'fr')">
                                        🇫🇷 Français
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('lang.switch', 'ar')">
                                        🇹🇳 العربية
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>

                        <!-- Settings Dropdown -->
                        <div class="hidden sm:flex sm:items-center sm:ml-4">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-400 bg-gray-800 hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        <div>{{ Auth::user()->name }}</div>

                                        <div class="ml-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
                                    </x-dropdown-link>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault();
                                                            this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-400 hover:bg-gray-900 focus:outline-none focus:bg-gray-900 focus:text-gray-400 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        @if(Auth::user()->isAdmin())
                            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                {{ __('Dashboard') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                                {{ __('Clients') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                                {{ __('Products') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                                {{ __('Orders') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')">
                                {{ __('Payments') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('claims.index')" :active="request()->routeIs('claims.*')">
                                {{ __('Warranty Claims') }}
                            </x-responsive-nav-link>
                        @else
                            <x-responsive-nav-link :href="route('client.dashboard')" :active="request()->routeIs('client.dashboard')">
                                {{ __('Dashboard') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('client.products.index')" :active="request()->routeIs('client.products.*')">
                                {{ __('My Products') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('client.payments.index')" :active="request()->routeIs('client.payments.*')">
                                {{ __('My Payments') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('client.contact')" :active="request()->routeIs('client.contact')">
                                {{ __('Support') }}
                            </x-responsive-nav-link>
                        @endif
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-700">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-200">{{ Auth::user()->name }}</div>
                            <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <x-responsive-nav-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-responsive-nav-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-responsive-nav-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-responsive-nav-link>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white/50 backdrop-blur-md border-b border-slate-200/60 sticky top-16 z-40">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-grow">
                {{ $slot }}
            </main>

            <footer class="bg-white border-t border-slate-200 py-12 relative z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                        <!-- Brand -->
                        <div class="col-span-1 md:col-span-2 space-y-4">
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
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
                            &copy; {{ date('Y') }} Finix CRM. All rights reserved.
                        </p>
                        <div class="flex gap-6">
                            <span class="text-[10px] text-slate-300 font-black uppercase tracking-tighter">Premium SaaS Solution</span>
                            <span class="text-[10px] text-slate-300 font-black uppercase tracking-tighter">Tunisia</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        @livewireScripts
    </body>
</html>
