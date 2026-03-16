@php
    /** @var \App\Models\Order $order */
@endphp
<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Product Details') }}
            </h2>
            <a href="{{ route('client.products.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                &larr; {{ __('Back to Products') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Overview Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $order->product->name ?? 'Unknown' }}</h3>
                            <div class="mt-2 text-gray-500">
                                @if($order->internal_note && env('APP_DEBUG') == true)
                                    <!-- Only showing this if debug is somehow engaged or if we want clients to see notes. Actually, product spec says hide admin notes. -->
                                @endif
                                <p class="text-sm">{{ __('Purchased on') }} {{ \Carbon\Carbon::parse($order->purchase_date)->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <div class="mt-4 md:mt-0">
                            @php
                                $status = $order->dynamic_status;
                                $bgClass = 'bg-gray-100 text-gray-800';
                                if ($status === 'Active') $bgClass = 'bg-green-100 text-green-800';
                                if ($status === 'Expiring Soon') $bgClass = 'bg-orange-100 text-orange-800';
                                if ($status === 'Expired') $bgClass = 'bg-red-100 text-red-800';
                                if ($status === 'Payment Pending') $bgClass = 'bg-yellow-100 text-yellow-800';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $bgClass }}">
                                {{ __($status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Client Visible Custom Fields -->
                    @php
                        $visibleFields = $order->fieldValues->filter(function($fv) {
                            return $fv->field && $fv->field->is_client_visible;
                        });
                    @endphp

                    @if($visibleFields->count() > 0)
                        <div class="mb-8 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-100 dark:border-indigo-800">
                            <h4 class="text-sm font-semibold uppercase tracking-wider text-indigo-800 dark:text-indigo-400 mb-3">{{ __('Service Setup Details') }}</h4>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
                                @foreach($visibleFields as $fv)
                                    <div class="sm:col-span-1 border-b border-indigo-100 dark:border-indigo-800/50 pb-2">
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $fv->field->label }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white break-words">
                                            @if($fv->field->type === 'url' && $fv->value)
                                                <a href="{{ $fv->value }}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1">
                                                    {{ $fv->value }}
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                </a>
                                            @elseif($fv->field->type === 'checkbox')
                                                {{ $fv->value ? __('Yes') : __('No') }}
                                            @else
                                                {{ $fv->value ?: '-' }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="text-lg font-semibold mb-3">{{ __('Contract Overview') }}</h4>
                            <dl class="space-y-2 text-sm md:text-base">
                                <div class="grid grid-cols-3 py-1 border-b border-gray-100 dark:border-gray-700">
                                    <dt class="text-gray-500">{{ __('Purchase Date') }}:</dt>
                                    <dd class="col-span-2 font-medium">{{ \Carbon\Carbon::parse($order->purchase_date)->format('M d, Y') }}</dd>
                                </div>
                                <div class="grid grid-cols-3 py-1 border-b border-gray-100 dark:border-gray-700">
                                    <dt class="text-gray-500">{{ __('Expiry Date') }}:</dt>
                                    <dd class="col-span-2 font-medium">{{ \Carbon\Carbon::parse($order->expiry_date)->format('M d, Y') }}</dd>
                                </div>
                                @if($order->duration)
                                <div class="grid grid-cols-3 py-1 border-b border-gray-100 dark:border-gray-700">
                                    <dt class="text-gray-500">{{ __('Duration') }}:</dt>
                                    <dd class="col-span-2 font-medium">{{ $order->duration }}</dd>
                                </div>
                                @endif
                                <div class="grid grid-cols-3 py-1">
                                    <dt class="text-gray-500">{{ __('Total Valid Price') }}:</dt>
                                    <dd class="col-span-2 font-medium">{{ $order->client->formatAmount($order->price) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold mb-3">{{ __('Payment Summary') }}</h4>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">{{ __('Amount Paid') }}:</dt>
                                        <dd class="font-medium text-green-600">{{ $order->client->formatAmount($order->paid_amount) }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">{{ __('Amount Pending') }}:</dt>
                                        <dd class="font-medium {{ $order->pending_amount > 0 ? 'text-red-500 font-bold' : 'text-gray-700' }}">{{ $order->client->formatAmount($order->pending_amount) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            @if($status === 'Expired' || $status === 'Expiring Soon')
                                <div class="mt-4 bg-orange-50 border border-orange-200 dark:bg-orange-900/20 dark:border-orange-900 rounded-lg p-4">
                                    <h5 class="text-orange-800 dark:text-orange-400 font-semibold mb-2">{{ __('Renewal Action Needed') }}</h5>
                                    <p class="text-sm text-orange-700 dark:text-orange-300 mb-3">{{ __('Your subscription is') }} {{ strtolower(__($status)) }}. {{ __("Let us know if you'd like to renew and maintain your service.") }}</p>
                                    <button class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-bold py-2 px-4 rounded w-full transition" onclick="alert('{{ __('In a real app, this would route to a Stripe Checkout or generate a renewal ticket/email.') }}')">
                                        {{ __('Renew Now') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warranty & Support Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4 mb-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Warranty & Protection') }}</h3>
                        </div>

                        <div>
                            @php
                                $wStatus = $order->warranty_status;
                                $wClass = 'bg-gray-100 text-gray-800';
                                if ($wStatus === 'Under Warranty') $wClass = 'bg-green-100 text-green-800';
                                if ($wStatus === 'Warranty Expiring Soon') $wClass = 'bg-yellow-100 text-yellow-800';
                                if ($wStatus === 'Warranty Expired') $wClass = 'bg-red-100 text-red-800';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $wClass }}">
                                {{ __($wStatus) }}
                            </span>
                        </div>
                    </div>

                    @if($order->warranty_enabled)
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-2">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-700">
                                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">{{ __('Valid From') }}</p>
                                        <p class="text-lg font-bold">{{ $order->warranty_start_date ? $order->warranty_start_date->format('d M Y') : 'N/A' }}</p>
                                    </div>
                                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-700">
                                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">{{ __('Protection Until') }}</p>
                                        <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $order->warranty_end_date ? $order->warranty_end_date->format('d M Y') : 'N/A' }}</p>
                                    </div>
                                </div>

                                <div class="prose dark:prose-invert max-w-none">
                                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">{{ __('Coverage Terms') }}</h4>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                        {!! nl2br(e($order->warranty_terms_snapshot ?: __('Default warranty terms apply to this product.'))) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl shadow-sm">
                                    <h4 class="text-blue-800 dark:text-blue-400 font-bold mb-2 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ __('Need Support?') }}
                                    </h4>
                                    @if(session()->has('message'))
                                        <div class="mb-4 p-2 bg-green-100 text-green-800 text-xs rounded border border-green-200">
                                            {{ session('message') }}
                                        </div>
                                    @endif

                                    <p class="text-xs text-blue-700 dark:text-blue-300 mb-4">{{ __('If you are experiencing issues with this product while under warranty, you can submit a claim below.') }}</p>
                                    
                                    @if($order->warranty_status === 'Under Warranty' || $order->warranty_status === 'Warranty Expiring Soon')
                                        @if(!$showClaimForm)
                                            <button wire:click="toggleClaimForm" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2.5 px-4 rounded-lg shadow-md transition">
                                                {{ __('Submit Warranty Claim') }}
                                            </button>
                                        @else
                                            <form wire:submit.prevent="submitClaim" class="space-y-3 bg-white dark:bg-gray-900 p-4 rounded-lg border border-blue-200 dark:border-blue-900 shadow-sm">
                                                <div>
                                                    <x-input-label for="claim_subject" :value="__('Subject')" class="text-[10px] uppercase" />
                                                    <x-text-input wire:model="subject" id="claim_subject" type="text" class="block mt-1 w-full text-xs" placeholder="{{ __('Brief summary of the issue') }}" required />
                                                    <x-input-error :messages="$errors->get('subject')" class="mt-1" />
                                                </div>
                                                <div>
                                                    <x-input-label for="claim_desc" :value="__('Description')" class="text-[10px] uppercase" />
                                                    <textarea wire:model="description" id="claim_desc" rows="3" class="block mt-1 w-full text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" placeholder="{{ __('Detailed description of the problem...') }}" required></textarea>
                                                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                                                </div>
                                                <div class="flex gap-2">
                                                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white text-[10px] font-bold py-2 rounded uppercase tracking-wider">
                                                        {{ __('Send Claim') }}
                                                    </button>
                                                    <button type="button" wire:click="toggleClaimForm" class="px-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-[10px] font-bold py-2 rounded uppercase tracking-wider">
                                                        {{ __('Cancel') }}
                                                    </button>
                                                </div>
                                            </form>
                                        @endif
                                    @else
                                        <p class="text-xs text-center p-2 bg-gray-200 dark:bg-gray-800 text-gray-500 rounded font-bold italic">{{ __('Warranty has expired') }}</p>
                                    @endif
                                </div>

                                <!-- Mini Claims List -->
                                @if($order->warrantyClaims->count() > 0)
                                    <div class="mt-4">
                                        <h4 class="text-sm font-bold mb-3">{{ __('Recent Claims') }}</h4>
                                        <div class="space-y-2">
                                            @foreach($order->warrantyClaims as $claim)
                                                <div class="p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-xs">
                                                    <div class="flex justify-between mb-1">
                                                        <span class="font-bold truncate pr-2">{{ $claim->subject }}</span>
                                                        <span class="text-[10px] px-1.5 py-0.5 rounded uppercase font-bold {{ $claim->status === 'open' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                                            {{ __($claim->status) }}
                                                        </span>
                                                    </div>
                                                    <p class="text-gray-500">{{ $claim->created_at->format('d M Y') }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <p class="text-gray-500 font-medium">{{ __('No warranty is associated with this product purchase') }}.</p>
                            <p class="text-xs text-gray-400 mt-1">{{ __('Check your contract terms for more information') }}.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment Logs for this Product -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Payment Logs') }}</h4>
                    @if($order->payments->count() === 0)
                        <p class="text-gray-500 italic">{{ __('No payments have been logged for this product') }}.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">{{ __('Date') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Amount') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Method') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Status') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Proofs') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->payments as $payment)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $order->client->formatAmount($payment->amount) }}</td>
                                            <td class="px-6 py-4">{{ __($payment->payment_method) }}</td>
                                            <td class="px-6 py-4">
                                                @if($payment->status === 'completed')
                                                    <span class="text-green-600">{{ __('Completed') }}</span>
                                                @elseif($payment->status === 'pending')
                                                    <span class="text-yellow-600">{{ __('Pending') }}</span>
                                                @else
                                                    <span class="text-red-600">{{ __('Failed') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 flex flex-col gap-1">
                                                @forelse($payment->proofs as $proof)
                                                    <a href="{{ Storage::url($proof->file_path) }}" target="_blank" class="text-blue-600 hover:underline text-xs flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                        {{ __('Review') }}
                                                    </a>
                                                @empty
                                                    <span class="text-gray-400 text-xs">-</span>
                                                @endforelse
                                            </td>
                                        </tr>
                                    @endforeach

                                    <!-- Credit Usage Allocations -->
                                    @foreach($order->allocations->whereNotNull('balance_transaction_id') as $allocation)
                                        <tr class="bg-indigo-50/50 dark:bg-indigo-900/10 border-b dark:border-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $allocation->created_at->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 font-bold text-indigo-700 dark:text-indigo-300">{{ $order->client->formatAmount($allocation->amount) }}</td>
                                            <td class="px-6 py-4 text-xs italic">{{ __('Applied from Credit Balance') }}</td>
                                            <td class="px-6 py-4 text-green-600 font-bold">{{ __('Applied') }}</td>
                                            <td class="px-6 py-4 text-gray-400 text-xs">{{ __('Credit Usage') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
