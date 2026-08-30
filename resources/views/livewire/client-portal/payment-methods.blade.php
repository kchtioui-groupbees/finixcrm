<div x-data="{
    copy(text, btn) {
        navigator.clipboard.writeText(text);
        let oldText = btn.innerText;
        btn.innerText = `{{ __('Copied!') }}`;
        btn.classList.add(`bg-green-100`, `text-green-700`, `border-green-200`);
        setTimeout(() => {
            btn.innerText = oldText;
            btn.classList.remove(`bg-green-100`, `text-green-700`, `border-green-200`);
        }, 2000);
    }
}" class="relative">

    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-900 leading-tight">
            {{ __('💳 Payment Methods') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto space-y-6">
                <div class="inline-flex items-center justify-center p-4 bg-indigo-50 rounded-2xl text-indigo-600 mb-2">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-4xl font-black text-slate-900 tracking-tight">{{ __('Payment Methods') }}</h3>
                <p class="text-lg text-slate-500 font-medium leading-relaxed">
                    {{ __('We offer several payment methods') }}
                </p>
            </div>

            @forelse($categoryLabels as $categoryKey => $categoryLabel)
                @if($methodsByCategory->has($categoryKey))
                    <div class="border-t border-slate-200/60"></div>

                    <section class="space-y-10">
                        <div class="flex items-center gap-4">
                            <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ __($categoryLabel) }}</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:ml-12 border-l-2 border-slate-100 pl-6 lg:pl-8">
                            @foreach($methodsByCategory->get($categoryKey) as $method)
                                <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition flex flex-col justify-between" wire:key="public-method-{{ $method->id }}">
                                    <div class="space-y-6">
                                        <div class="flex items-center gap-3">
                                            @if($method->logo_path)
                                                <img src="{{ Storage::url($method->logo_path) }}" class="w-12 h-12 rounded-2xl object-cover border border-slate-100">
                                            @else
                                                <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 font-black text-lg border border-indigo-100">{{ mb_substr($method->label, 0, 2) }}</div>
                                            @endif
                                            <h4 class="text-xl font-black text-slate-900">{{ $method->label }}</h4>
                                        </div>

                                        @if($method->description)
                                            <p class="text-sm text-slate-500">{{ $method->description }}</p>
                                        @endif

                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach(($method->currencies ?? []) as $cur)
                                                <span class="text-[10px] font-black bg-slate-100 text-slate-600 px-2 py-1 rounded-full uppercase tracking-widest">{{ $cur }}</span>
                                            @endforeach
                                        </div>

                                        @if($method->fields->isNotEmpty())
                                            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-3">
                                                @foreach($method->fields as $field)
                                                    <div class="{{ !$loop->first ? 'border-t border-slate-200/50 pt-3' : '' }}">
                                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $field->label }}</div>
                                                        <div class="flex items-center justify-between gap-2">
                                                            <div class="text-base font-mono font-black text-slate-800 break-all">
                                                                @if($field->type === 'link' && $field->value)
                                                                    <a href="{{ $field->value }}" target="_blank" class="text-indigo-600 underline">{{ $field->value }}</a>
                                                                @else
                                                                    {{ $field->value }}
                                                                @endif
                                                            </div>
                                                            @if($field->copyable && $field->value)
                                                                <button type="button" @click="copy('{{ addslashes($field->value) }}', $el)" class="shrink-0 text-[10px] font-bold border-2 border-slate-200 hover:border-slate-300 text-slate-600 px-2 py-1 rounded-lg transition">
                                                                    {{ __('Copy') }}
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($method->instructions)
                                            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl flex items-start gap-3">
                                                <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <div class="text-xs text-slate-600 font-medium">{{ $method->instructions }}</div>
                                            </div>
                                        @endif

                                        @if($method->fee_type === 'unknown')
                                            <div class="bg-amber-50 border border-amber-100 p-3 rounded-xl text-xs text-amber-700 font-medium">
                                                {{ $method->fee_summary }}
                                            </div>
                                        @elseif($method->fee_type !== 'none')
                                            <div class="bg-amber-50 border border-amber-100 p-3 rounded-xl text-xs font-black text-amber-800 uppercase tracking-widest">
                                                {{ __('Fee') }}: {{ $method->fee_summary }} — {{ __('paid by') }} {{ $method->fee_paid_by_label }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @empty
            @endforelse

            @if($methodsByCategory->isEmpty())
                <div class="text-center text-slate-400 italic py-20">{{ __('No payment methods are configured yet.') }}</div>
            @endif

            <!-- Contact -->
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
</div>
