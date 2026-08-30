<x-public-layout :title="__('About') . ' — Finix CRM'">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        <div class="text-center space-y-6">
            <div class="inline-flex items-center justify-center p-4 bg-indigo-50 rounded-2xl text-indigo-600 mb-2">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">{{ __('About Finix') }}</h1>
        </div>

        <div class="bg-white rounded-3xl p-8 md:p-12 border border-slate-200 shadow-sm space-y-6 text-slate-600 font-medium leading-relaxed text-base">
            <p>Bienvenue chez Finix, votre partenaire de confiance pour accéder à des licences, abonnements et services numériques à prix avantageux.</p>

            <p>Finix accompagne les particuliers et les entreprises dans leurs besoins numériques : logiciels, outils d'intelligence artificielle, VPN, services de streaming et autres solutions digitales.</p>

            <p>Notre objectif est de rendre la technologie plus accessible, tout en proposant une expérience d'achat simple, rapide et transparente.</p>

            <p>Nous sélectionnons nos offres auprès de sources que nous considérons fiables et nous fournissons les informations nécessaires concernant les conditions d'utilisation, la durée, la compatibilité et l'activation de chaque service.</p>

            <div class="bg-slate-50 border-l-4 border-finix-purple rounded-2xl p-6">
                <p class="font-black text-slate-900 mb-2">{{ __('Our mission is simple:') }}</p>
                <p class="italic">Vous faire gagner du temps et de l'argent tout en vous accompagnant à chaque étape, du choix du produit jusqu'à son activation.</p>
            </div>

            <p>Notre équipe reste disponible pour vous conseiller, vous assister et vous guider en cas de difficulté.</p>

            <p class="font-bold text-slate-900">Faites confiance à Finix pour vos besoins numériques : une solution simple, pratique et économique.</p>
        </div>

        <div class="bg-slate-900 rounded-3xl p-8 md:p-12 text-white relative overflow-hidden shadow-2xl">
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500 rounded-full mix-blend-screen filter blur-[100px] opacity-30"></div>
            <div class="relative z-10 space-y-6">
                <h3 class="text-2xl font-black">{{ __('Contact') }}</h3>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="mailto:contact@finix.tn" class="flex items-center gap-3 bg-white/10 hover:bg-white/20 transition rounded-2xl px-6 py-4 font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        contact@finix.tn
                    </a>
                    <a href="https://wa.me/21692871752" target="_blank" class="flex items-center gap-3 bg-emerald-500 hover:bg-emerald-400 transition rounded-2xl px-6 py-4 font-bold">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.341-4.341 9.947-9.886 9.947M12 0C5.373 0 0 5.373 0 12c0 2.123.55 4.117 1.511 5.86L0 24l6.337-1.663c1.677.91 3.593 1.398 5.663 1.398 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"></path></svg>
                        <span dir="ltr">&lrm;+216 92 871 752</span>
                    </a>
                </div>
            </div>
        </div>

    </div>
</x-public-layout>
