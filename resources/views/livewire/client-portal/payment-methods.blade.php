<div x-data="{ 
    qrModal: false, 
    qrImg: ``, 
    qrTitle: ``,
    copy(text, btn) {
        navigator.clipboard.writeText(text);
        let oldText = btn.innerText;
        btn.innerText = `{{ __('Copied!') }}`;
        btn.classList.add(`bg-green-100`, `text-green-700`, `border-green-200`);
        setTimeout(() => {
            btn.innerText = oldText;
            btn.classList.remove(`bg-green-100`, `text-green-700`, `border-green-200`);
        }, 2000);
    },
    openQr(title, img) {
        this.qrTitle = title;
        this.qrImg = img;
        this.qrModal = true;
    }
}" class="relative">

    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-900 leading-tight">
            {{ __('💳 Payment Methods') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
            
            <!-- Header Section -->
            <div class="text-center max-w-3xl mx-auto space-y-6">
                <div class="inline-flex items-center justify-center p-4 bg-indigo-50 rounded-2xl text-indigo-600 mb-2">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-4xl font-black text-slate-900 tracking-tight">{{ __('Payment Methods') }}</h3>
                <p class="text-lg text-slate-500 font-medium leading-relaxed">
                    {{ __('We offer several payment methods') }}
                </p>
                <div class="pt-4">
                    <a href="https://wa.me/21692871752" target="_blank" class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 font-bold py-3 px-6 rounded-xl border border-emerald-200 hover:bg-emerald-100 transition shadow-sm">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.341-4.341 9.947-9.886 9.947M12 0C5.373 0 0 5.373 0 12c0 2.123.55 4.117 1.511 5.86L0 24l6.337-1.663c1.677.91 3.593 1.398 5.663 1.398 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"></path></svg>
                        <span class="inline-block" dir="ltr">&lrm;WhatsApp: +216 92871752</span>
                    </a>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-slate-200/60"></div>

            <!-- SECTION 1: Tunisia Payments -->
            <section class="space-y-10">
                <div class="flex items-center gap-4">
                    <span class="p-3 bg-red-50 text-red-500 rounded-xl">🇹🇳</span>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ __('Tunisia Payments') }}</h2>
                </div>

                <!-- Mobile Payments -->
                <div class="space-y-6 lg:ml-12 border-l-2 border-slate-100 pl-6 lg:pl-8">
                    <h3 class="text-lg font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        {{ __('Mobile Payments') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- D17 -->
                        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between relative overflow-hidden group">
                            <div class="absolute top-4 right-4 bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase px-3 py-1 rounded-full tracking-widest">{{ __('Recommended') }}</div>
                            <div class="space-y-6 relative z-10">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 font-black text-xl border border-emerald-100">D17</div>
                                    <h4 class="text-2xl font-black text-slate-900">D17</h4>
                                </div>
                                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-2">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Phone Number') }}</div>
                                    <div class="text-xl font-mono font-black text-slate-800"><span class="inline-block" dir="ltr">&lrm;+216 92871752</span></div>
                                </div>
                                <div class="bg-amber-50 border border-amber-100 p-4 rounded-2xl">
                                    <div class="text-xs font-black text-amber-800 uppercase tracking-widest mb-1 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ __('Transfer Fee') }}: +1%
                                    </div>
                                    <div class="text-xs text-amber-700/80 font-medium italic mt-2 border-t border-amber-200/50 pt-2">
                                        {{ __('Example: If the payment is 100 TND you must send 101 TND.') }}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-8 flex gap-3 relative z-10">
                                <button type="button" @click="copy('+21692871752', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                    {{ __('Copy Number') }}
                                </button>
                                <button type="button" @click="openQr('D17', '/img/qr/d17.png')" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    {{ __('Show QR') }}
                                </button>
                            </div>
                        </div>

                        <!-- Flouci -->
                        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between">
                            <div class="space-y-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-500 font-black text-xl border border-orange-100">FL</div>
                                    <h4 class="text-2xl font-black text-slate-900">Flouci</h4>
                                </div>
                                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-4">
                                    <div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Phone Number') }}</div>
                                        <div class="text-xl font-mono font-black text-slate-800" dir="ltr">25208023</div>
                                    </div>
                                    <div class="border-t border-slate-200/50 pt-4">
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Account Holder') }}</div>
                                        <div class="text-base font-bold text-slate-700">Dhia Boubaker</div>
                                    </div>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl flex items-start gap-3">
                                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div class="text-sm text-slate-600 font-medium">{{ __('Add Flouci transfer fees.') }}</div>
                                </div>
                            </div>
                            <div class="mt-8 flex gap-3">
                                <button type="button" @click="copy('25208023', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                    {{ __('Copy Number') }}
                                </button>
                                <button type="button" @click="openQr('Flouci', '/img/qr/flouci.png')" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    {{ __('Show QR') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Transfers -->
                <div class="space-y-6 lg:ml-12 border-l-2 border-slate-100 pl-6 lg:pl-8">
                    <h3 class="text-lg font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        {{ __('Bank Transfers') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- BTE -->
                        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between">
                            <div class="space-y-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 font-black border border-blue-100">🏦</div>
                                    <div>
                                        <h4 class="text-xl font-black text-slate-900">{{ __('BTE Bank') }}</h4>
                                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ __('Banque de Tunisie et des Emirats') }}</p>
                                    </div>
                                </div>
                                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-4">
                                    <div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Account Holder') }}</div>
                                        <div class="text-base font-bold text-slate-700">Dhia Boubaker</div>
                                    </div>
                                    <div class="border-t border-slate-200/50 pt-4">
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('RIB') }}</div>
                                        <div class="text-lg font-mono font-black text-slate-800 break-all" dir="ltr">24 031 188 6832 511101 36</div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl flex items-start gap-3">
                                        <svg class="w-4 h-4 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <div class="text-xs text-slate-600 font-medium">{{ __('Use only for transfers from another bank or banking application.') }}</div>
                                    </div>
                                    <div class="bg-rose-50 border border-rose-100 p-3 rounded-xl flex items-start gap-3">
                                        <svg class="w-4 h-4 text-rose-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        <div class="text-xs text-rose-700 font-bold">{{ __('Do not deposit cash because this is an online bank.') }}</div>
                                    </div>
                                </div>
                            </div>
                             <div class="mt-8 flex gap-3">
                                 <button type="button" @click="copy('Dhia Boubaker', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                     {{ __('Copy Holder') }}
                                 </button>
                                 <button type="button" @click="copy('24031188683251110136', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                     {{ __('Copy RIB') }}
                                 </button>
                             </div>
                        </div>

                        <!-- Postal Bank -->
                        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between">
                            <div class="space-y-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-yellow-50 rounded-2xl flex items-center justify-center text-yellow-600 font-black border border-yellow-100">📮</div>
                                    <h4 class="text-xl font-black text-slate-900">{{ __('Postal Bank Account') }}</h4>
                                </div>
                                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-4">
                                    <div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Account Holder') }}</div>
                                        <div class="text-base font-bold text-slate-700">Khaled Chtioui</div>
                                    </div>
                                    <div class="border-t border-slate-200/50 pt-4">
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('RIB') }}</div>
                                        <div class="text-lg font-mono font-black text-slate-800 break-all" dir="ltr">17503000000367570544</div>
                                    </div>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl flex items-start gap-3">
                                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div class="text-sm text-slate-600 font-medium">{{ __('Transfer Fee: 1700 millimes') }}</div>
                                </div>
                            </div>
                             <div class="mt-8 flex gap-3">
                                 <button type="button" @click="copy('Khaled Chtioui', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                     {{ __('Copy Holder') }}
                                 </button>
                                 <button type="button" @click="copy('17503000000367570544', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                     {{ __('Copy RIB') }}
                                 </button>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Cash Agencies -->
                <div class="space-y-6 lg:ml-12 border-l-2 border-slate-100 pl-6 lg:pl-8">
                    <h3 class="text-lg font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-7h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        {{ __('Cash Agencies') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Wafa Cash -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex items-center gap-4">
                            <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500 font-black text-xs border border-amber-200 flex-shrink-0">Wafa</div>
                            <div>
                                <h4 class="text-lg font-black text-slate-900">Wafa Cash</h4>
                                <div class="text-xs text-slate-500 font-medium mt-1">
                                    {{ __('Name') }}: <span class="font-bold text-slate-800">Ridha Chtioui</span><br>
                                    {{ __('Phone') }}: <span class="font-bold text-slate-800">98435088</span><br>
                                    {{ __('Agency') }}: <span class="font-bold text-slate-800">Jemmel</span>
                                </div>
                            </div>
                        </div>

                        <!-- Izi Payement -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex items-center gap-4">
                            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-500 font-black text-xs border border-blue-200 flex-shrink-0">Izi</div>
                            <div>
                                <h4 class="text-lg font-black text-slate-900">Izi Payement</h4>
                                <div class="text-xs text-slate-500 font-medium mt-1">
                                    {{ __('Name') }}: <span class="font-bold text-slate-800">Ridha Chtioui</span><br>
                                    {{ __('Phone') }}: <span class="font-bold text-slate-800">98435088</span><br>
                                    {{ __('Agency') }}: <span class="font-bold text-slate-800">Jemmel</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>

            <!-- Divider -->
            <div class="border-t border-slate-200/60"></div>

            <!-- SECTION 2: International Payments -->
            <section class="space-y-10">
                <div class="flex items-center gap-4">
                    <span class="p-3 bg-indigo-50 text-indigo-500 rounded-xl">🌍</span>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ __('International Payments') }}</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:ml-12 border-l-2 border-slate-100 pl-6 lg:pl-8">
                    <!-- Wise -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between relative overflow-hidden group">
                        <div class="absolute top-4 right-4 bg-indigo-100 text-indigo-700 text-[10px] font-black uppercase px-3 py-1 rounded-full tracking-widest">{{ __('Recommended') }}</div>
                        <div class="space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-lime-50 rounded-2xl flex items-center justify-center text-lime-600 font-black text-xl border border-lime-100">W</div>
                                <h4 class="text-2xl font-black text-slate-900">Wise (Internal)</h4>
                            </div>
                            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-4">
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Wise ID') }}</div>
                                    <div class="text-lg font-mono font-black text-slate-800">426343455</div>
                                </div>
                                <div class="border-t border-slate-200/50 pt-4">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Wise Tag') }}</div>
                                    <div class="text-base font-bold text-slate-700">@khaledc292</div>
                                </div>
                            </div>
                            <div class="bg-indigo-50 border border-indigo-100 p-3 rounded-xl flex items-start gap-3">
                                <svg class="w-4 h-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <div class="text-xs text-indigo-800 font-medium">{{ __('Recommended for international clients.') }}</div>
                            </div>
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button type="button" @click="copy('426343455', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                {{ __('Copy ID') }}
                            </button>
                            <button type="button" @click="openQr('Wise', '/img/qr/wise.png')" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                {{ __('Show QR') }}
                            </button>
                        </div>
                    </div>

                    <!-- Wise USD -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between">
                        <div class="space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-lime-50 rounded-2xl flex items-center justify-center text-lime-600 font-black border border-lime-100">🇺🇸</div>
                                <h4 class="text-xl font-black text-slate-900">Wise (USD Account)</h4>
                            </div>
                            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-4">
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Account Name') }}</div>
                                    <div class="text-base font-bold text-slate-700">Khaled Chtioui</div>
                                </div>
                                <div class="border-t border-slate-200/50 pt-4">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Account Number') }}</div>
                                    <div class="text-lg font-mono font-black text-slate-800">478778214010881</div>
                                </div>
                                <div class="border-t border-slate-200/50 pt-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Routing Number') }}</div>
                                        <div class="text-sm font-mono font-black text-slate-700 uppercase">084009519</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Swift/BIC') }}</div>
                                        <div class="text-sm font-mono font-black text-slate-700 uppercase">TRWIUS35XXX</div>
                                    </div>
                                </div>
                                <div class="border-t border-slate-200/50 pt-4">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Bank Address') }}</div>
                                    <div class="text-[11px] text-slate-500 font-medium">{{ __('Wise US Inc, 108 W 13th St, Wilmington, DE, 19801, USA') }}</div>
                                </div>
                            </div>
                        </div>
                         <div class="mt-8 flex gap-3">
                             <button type="button" @click="copy('Khaled Chtioui', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                 {{ __('Copy Holder') }}
                             </button>
                             <button type="button" @click="copy('478778214010881', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                 {{ __('Copy Account Number') }}
                             </button>
                         </div>
                    </div>

                    <!-- Wise EUR -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between">
                        <div class="space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-lime-50 rounded-2xl flex items-center justify-center text-lime-600 font-black border border-lime-100">🇪🇺</div>
                                <h4 class="text-xl font-black text-slate-900">Wise (EUR Account)</h4>
                            </div>
                            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-4">
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Account Name') }}</div>
                                    <div class="text-base font-bold text-slate-700">Khaled Chtioui</div>
                                </div>
                                <div class="border-t border-slate-200/50 pt-4">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('IBAN') }}</div>
                                    <div class="text-lg font-mono font-black text-slate-800 break-all">BE79 9050 9314 2033</div>
                                </div>
                                <div class="border-t border-slate-200/50 pt-4">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Swift/BIC') }}</div>
                                    <div class="text-sm font-mono font-black text-slate-700 uppercase tracking-widest">TRWIBEB1XXX</div>
                                </div>
                                <div class="border-t border-slate-200/50 pt-4">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Bank Address') }}</div>
                                    <div class="text-[11px] text-slate-500 font-medium italic">{{ __('Wise, Rue du Trône 100, 3rd floor, Brussels, 1050, Belgium') }}</div>
                                </div>
                            </div>
                        </div>
                         <div class="mt-8 flex gap-3">
                             <button type="button" @click="copy('Khaled Chtioui', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                 {{ __('Copy Holder') }}
                             </button>
                             <button type="button" @click="copy('BE79905093142033', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                 {{ __('Copy IBAN') }}
                             </button>
                         </div>
                    </div>

                    <!-- PayPal -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between">
                        <div class="space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 font-black text-xl border border-blue-100">PP</div>
                                <h4 class="text-2xl font-black text-slate-900">PayPal</h4>
                            </div>
                            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-4">
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Account Name') }}</div>
                                    <div class="text-base font-bold text-slate-800">Khaled Chtioui</div>
                                </div>
                                <div class="border-t border-slate-200/50 pt-4">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('UserTag') }}</div>
                                    <div class="text-base font-bold text-slate-700">@khaledchtioui</div>
                                </div>
                                <div class="border-t border-slate-200/50 pt-4">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Email Address') }}</div>
                                    <div class="text-base font-bold text-slate-700">Khaledschmal@gmail.com</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button type="button" @click="copy('@khaledchtioui', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                {{ __('Copy Tag') }}
                            </button>
                            <button type="button" @click="copy('Khaledschmal@gmail.com', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                {{ __('Copy Email') }}
                            </button>
                        </div>
                        <div class="mt-4">
                            <button type="button" @click="openQr('PayPal', '/img/qr/paypal.png')" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                {{ __('Show QR') }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Divider -->
            <div class="border-t border-slate-200/60"></div>

            <!-- SECTION 3: Crypto Payment -->
            <section class="space-y-10">
                <div class="flex items-center gap-4">
                    <span class="p-3 bg-yellow-50 text-yellow-600 rounded-xl">₿</span>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ __('Crypto Payments') }}</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:ml-12 border-l-2 border-slate-100 pl-6 lg:pl-8">
                    <!-- Binance Pay -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between">
                        <div class="space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-[#FCD535] rounded-2xl flex items-center justify-center text-slate-900 font-black text-xl border border-yellow-400">BP</div>
                                <h4 class="text-2xl font-black text-slate-900">Binance Pay</h4>
                            </div>
                            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-4">
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('Binance Pay ID') }}</div>
                                    <div class="text-lg font-mono font-black text-slate-800">426343455</div>
                                </div>
                            </div>
                            <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl flex items-start gap-3">
                                <svg class="w-5 h-5 text-slate-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <div class="text-sm text-slate-600 font-bold">{{ __('Scan the QR code using the Binance app to complete payment.') }}</div>
                            </div>
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button type="button" @click="copy('426343455', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                {{ __('Copy ID') }}
                            </button>
                            <button type="button" @click="openQr('Binance Pay', '/img/qr/binance.png')" class="flex-1 bg-[#FCD535] hover:bg-yellow-400 text-slate-900 font-black py-3 rounded-xl transition text-sm flex items-center justify-center gap-3 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                {{ __('Show QR') }}
                            </button>
                        </div>
                    </div>

                    <!-- RedotPay (Moved to Crypto) -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between">
                        <div class="space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center text-red-600 font-black text-xl border border-red-100">R</div>
                                <h4 class="text-2xl font-black text-slate-900">RedotPay</h4>
                            </div>
                            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-4">
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ __('RedotPay ID') }}</div>
                                    <div class="text-lg font-mono font-black text-slate-800">1105050034</div>
                                </div>
                            </div>
                            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl flex items-start gap-3">
                                <svg class="w-4 h-4 text-slate-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <div class="text-xs text-slate-600 font-medium">{{ __('Scan with the RedotPay app to pay.') }}</div>
                            </div>
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button type="button" @click="copy('1105050034', $el)" class="flex-1 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-2.5 rounded-xl transition text-sm">
                                {{ __('Copy ID') }}
                            </button>
                            <button type="button" @click="openQr('RedotPay', '/img/qr/redotpay.png')" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                {{ __('Show QR') }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 4: Payment Instructions -->
            <section class="bg-slate-900 rounded-3xl p-8 md:p-12 text-white relative overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500 rounded-full mix-blend-screen filter blur-[100px] opacity-30"></div>
                <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
                    <div class="space-y-6">
                        <h3 class="text-3xl font-black">{{ __('After making your payment') }}</h3>
                        <ol class="space-y-4">
                            <li class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center font-black flex-shrink-0 mt-0.5">1</div>
                                <p class="text-slate-300 font-medium">{{ __('Take a screenshot / photo of the payment confirmation.') }}</p>
                            </li>
                            <li class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center font-black flex-shrink-0 mt-0.5">2</div>
                                <p class="text-slate-300 font-medium">{{ __('Send the screenshot via WhatsApp.') }}</p>
                            </li>
                            <li class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-black flex-shrink-0 mt-0.5">3</div>
                                <p class="text-white font-bold">{{ __('Your order will be activated after verification!') }}</p>
                            </li>
                        </ol>
                    </div>
                    <div class="flex justify-center flex-col items-center md:items-end gap-4">
                        <a href="https://wa.me/21692871752" target="_blank" class="w-full md:w-auto text-center bg-emerald-500 hover:bg-emerald-400 text-white font-black py-4 px-8 rounded-2xl transition shadow-lg shadow-emerald-500/30 flex items-center justify-center gap-3">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.341-4.341 9.947-9.886 9.947M12 0C5.373 0 0 5.373 0 12c0 2.123.55 4.117 1.511 5.86L0 24l6.337-1.663c1.677.91 3.593 1.398 5.663 1.398 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"></path></svg>
                            <span class="inline-block" dir="ltr">&lrm;{{ __('Send proof via WhatsApp') }}</span>
                        </a>
                        <p class="text-sm text-slate-400 font-medium" style="direction: ltr !important;"><span class="inline-block" dir="ltr">&lrm;WhatsApp: +216 92871752</span></p>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <!-- QR Code Modal -->
    <div x-show="qrModal" 
         x-transition.opacity
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4">
        
        <div @click.away="qrModal = false" 
             x-show="qrModal"
             x-transition.scale.origin.bottom
             class="bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full shadow-2xl relative overflow-hidden text-center">
            
            <button @click="qrModal = false" type="button" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-full transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <h3 class="text-xl font-black text-slate-900 mb-6" x-text="qrTitle"></h3>
            
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex justify-center items-center select-none">
                <img :src="qrImg" :alt="'QR Code ' + qrTitle" class="max-w-[240px] w-full h-auto rounded-xl shadow-sm mix-blend-multiply">
            </div>

            <button @click="qrModal = false" type="button" class="mt-6 w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition">
                {{ __('Close') }}
            </button>
        </div>
    </div>
</div>
