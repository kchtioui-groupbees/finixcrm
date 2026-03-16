<x-public-layout>
    <x-slot name="title">Payment Methods - FinixTN CRM</x-slot>

    <section class="py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl lg:text-5xl font-black text-slate-900 mb-8 italic">Accepted Payment Methods</h1>
            <p class="text-lg text-slate-600 font-medium leading-relaxed mb-16">
                We offer flexible payment options to suit your business needs in Tunisia and worldwide.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
                <div class="p-8 rounded-3xl bg-white border border-slate-100 shadow-sm group hover:border-blue-200 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 mb-6 text-xl">💳</div>
                    <h3 class="font-bold text-xl mb-4">Bank Transfer (Tunisia)</h3>
                    <ul class="text-slate-500 text-sm font-medium space-y-2">
                        <li>• Direct Bank Transfer (Virement)</li>
                        <li>• Mandat Minute (Poste Tunisienne)</li>
                        <li>• D17 Transfer</li>
                    </ul>
                </div>

                <div class="p-8 rounded-3xl bg-white border border-slate-100 shadow-sm group hover:border-purple-200 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 mb-6 text-xl">🌍</div>
                    <h3 class="font-bold text-xl mb-4">International Options</h3>
                    <ul class="text-slate-500 text-sm font-medium space-y-2">
                        <li>• Stripe (Credit/Debit Cards)</li>
                        <li>• PayPal</li>
                        <li>• Wise Transfer</li>
                    </ul>
                </div>

                <div class="p-8 rounded-3xl bg-white border border-slate-100 shadow-sm group hover:border-amber-200 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 mb-6 text-xl">🪙</div>
                    <h3 class="font-bold text-xl mb-4">Crypto Payments</h3>
                    <ul class="text-slate-500 text-sm font-medium space-y-2">
                        <li>• USDT (BEP20 / TRC20)</li>
                        <li>• Bitcoin</li>
                        <li>• Ethereum</li>
                    </ul>
                </div>

                <div class="p-8 rounded-3xl bg-finix-purple text-white shadow-xl shadow-purple-100">
                    <h3 class="font-bold text-xl mb-4 italic">Need Custom Options?</h3>
                    <p class="text-white/80 text-sm font-medium mb-6">Contact our support team for customized billing arrangements.</p>
                    <a href="{{ route('public.contact') }}" class="inline-block px-6 py-2 bg-white text-finix-purple rounded-lg font-bold text-sm">Contact Us</a>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
