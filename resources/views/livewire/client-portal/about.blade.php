<div>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-900 leading-tight">
            {{ __('ℹ️ About - FinixTN') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="premium-card bg-white p-10 space-y-10 relative overflow-hidden">
                <!-- Decorative background elements -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50 rounded-full -mr-32 -mt-32 blur-3xl opacity-50"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-50 rounded-full -ml-32 -mb-32 blur-3xl opacity-50"></div>

                <div class="relative z-10 space-y-8">
                    <div class="text-center space-y-4">
                        <div class="inline-block px-4 py-1.5 bg-indigo-50 text-indigo-600 rounded-full text-xs font-black uppercase tracking-widest mb-2">{{ __('Our Vision') }}</div>
                        <h3 class="text-4xl font-black text-slate-900 tracking-tight">{{ __('Digital excellence at your fingertips') }}.</h3>
                        <p class="text-lg text-slate-500 font-medium max-w-2xl mx-auto">
                            {{ __('FinixTN is more than just a resale platform. We are your strategic partner for navigating the digital world.') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6">
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest border-l-2 border-indigo-500 pl-3">{{ __('Our Specialization') }}</h4>
                            <ul class="space-y-3">
                                <li class="flex items-center gap-3 text-slate-700 font-bold">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                                    {{ __('Professional digital tools') }}
                                </li>
                                <li class="flex items-center gap-3 text-slate-700 font-bold">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                                    {{ __('Premium Subscriptions') }}
                                </li>
                                <li class="flex items-center gap-3 text-slate-700 font-bold">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                                    {{ __('Exclusive software bundles') }}
                                </li>
                                <li class="flex items-center gap-3 text-slate-700 font-bold">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                                    {{ __('Custom digital services') }}
                                </li>
                            </ul>
                        </div>
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest border-l-2 border-emerald-500 pl-3">{{ __('For whom?') }}</h4>
                            <ul class="space-y-3">
                                <li class="flex items-center gap-3 text-slate-700 font-bold">
                                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                    {{ __('Demanding individuals') }}
                                </li>
                                <li class="flex items-center gap-3 text-slate-700 font-bold">
                                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                    {{ __('Freelancers & Creators') }}
                                </li>
                                <li class="flex items-center gap-3 text-slate-700 font-bold">
                                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                    {{ __('Small and Medium Enterprises') }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="pt-10 border-t border-slate-100 italic text-slate-500 text-center font-medium">
                        "{{ __('Our goal is to provide reliable, fast and accessible services in Tunisia.') }}"
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
