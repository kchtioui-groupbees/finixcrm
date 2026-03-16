<div>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-900 leading-tight">
            {{ __('📞 Contact Us') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Contact Info left -->
                <div class="space-y-8">
                    <div class="space-y-4">
                        <h3 class="text-4xl font-black text-slate-900 tracking-tight">{{ __('Need help?') }} <br><span class="text-indigo-600">{{ __("We're here for you") }}.</span></h3>
                        <p class="text-lg text-slate-500 font-medium leading-relaxed">
                            {{ __('Our team is available to answer your technical or commercial questions. Feel free to contact us via WhatsApp for a quick response.') }}
                        </p>
                    </div>

                    <div class="space-y-4">
                        <a href="https://wa.me/21692871752" target="_blank" class="flex items-center gap-6 p-6 bg-emerald-50 rounded-3xl border border-emerald-100 hover:shadow-xl transition group">
                            <div class="w-16 h-16 bg-emerald-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition duration-300">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.341-4.341 9.947-9.886 9.947M12 0C5.373 0 0 5.373 0 12c0 2.123.55 4.117 1.511 5.86L0 24l6.337-1.663c1.677.91 3.593 1.398 5.663 1.398 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"></path></svg>
                            </div>
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-1">{{ __('Priority Contact') }}</div>
                                <div class="text-2xl font-black text-slate-900">+216 92871752</div>
                                <div class="text-xs text-emerald-700 font-bold uppercase tracking-tight">{{ __('Available on WhatsApp') }}</div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Right side Image or Card -->
                <div class="premium-card bg-slate-900 p-10 text-white space-y-8 relative overflow-hidden group">
                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-500 rounded-full blur-3xl opacity-20 group-hover:opacity-40 transition duration-700"></div>
                    
                    <div class="relative z-10 space-y-6">
                        <h4 class="text-xs font-black uppercase tracking-widest text-indigo-400">{{ __('Official Channels') }}</h4>
                        <div class="space-y-4">
                            <div class="flex items-center gap-4 p-4 bg-white/5 rounded-2xl border border-white/5">
                                <span class="text-2xl">📧</span>
                                <div>
                                    <div class="text-[10px] font-black uppercase text-slate-500">{{ __('Email Support') }}</div>
                                    <div class="text-lg font-bold">contact@finix.tn</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 p-4 bg-white/5 rounded-2xl border border-white/5">
                                <span class="text-2xl">🌍</span>
                                <div>
                                    <div class="text-[10px] font-black uppercase text-slate-500">{{ __('Headquarters') }}</div>
                                    <div class="text-lg font-bold">{{ __('Jemmel, Tunisia') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6">
                            <h4 class="text-xs font-black uppercase tracking-widest text-indigo-400 mb-4">{{ __('Information Channel') }}</h4>
                            <a href="https://whatsapp.com/channel/0029Vb7VZgFEQIasxa6AC12C" target="_blank" class="btn-phoenix w-full py-4 text-center justify-center">{{ __('Join WhatsApp Channel') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
