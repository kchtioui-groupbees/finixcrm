<x-public-layout>
    <x-slot name="title">Contact - FinixTN CRM</x-slot>

    <section class="py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h1 class="text-4xl lg:text-5xl font-black text-slate-900 mb-6 italic">Get in Touch</h1>
                <p class="text-lg text-slate-600 font-medium">Have questions? We're here to help you scaling your business.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="space-y-8">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 text-xl">📧</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Email Us</h4>
                            <p class="text-slate-500 font-medium text-sm">support@finix.tn</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl">📞</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Call Us</h4>
                            <p class="text-slate-500 font-medium text-sm">+216 92 871 752</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 text-xl">📍</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Office</h4>
                            <p class="text-slate-500 font-medium text-sm">Tunis, Tunisia</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 rounded-3xl bg-white border border-slate-100 shadow-sm">
                    <form action="#" class="space-y-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Name</label>
                            <input type="text" class="w-full px-4 py-3 rounded-xl border-slate-200 focus:border-finix-purple focus:ring-finix-purple" placeholder="John Doe">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Email</label>
                            <input type="email" class="w-full px-4 py-3 rounded-xl border-slate-200 focus:border-finix-purple focus:ring-finix-purple" placeholder="john@example.com">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Message</label>
                            <textarea rows="4" class="w-full px-4 py-3 rounded-xl border-slate-200 focus:border-finix-purple focus:ring-finix-purple" placeholder="How can we help?"></textarea>
                        </div>
                        <button type="submit" class="w-full py-4 rounded-xl bg-finix-purple text-white font-bold hover:bg-finix-purple-dark transition-colors shadow-lg shadow-purple-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
