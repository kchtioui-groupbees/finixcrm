<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Unpaid Clients') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Sub-list navigation -->
                    <div class="mb-6 flex flex-wrap gap-2">
                        <button wire:click="setTab('unpaid')" class="px-4 py-1.5 text-xs font-bold rounded-md {{ $activeTab === 'unpaid' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 dark:bg-gray-900 text-gray-500' }}">{{ __('Unpaid Orders') }}</button>
                        <button wire:click="setTab('partial')" class="px-4 py-1.5 text-xs font-bold rounded-md {{ $activeTab === 'partial' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 dark:bg-gray-900 text-gray-500' }}">{{ __('Partially Paid Orders') }}</button>
                        <a href="{{ route('payments.pending') }}" class="px-4 py-1.5 text-xs font-bold rounded-md bg-gray-100 dark:bg-gray-900 text-gray-500">{{ __('Pending Confirmation') }}</a>
                        <button wire:click="setTab('rejected')" class="px-4 py-1.5 text-xs font-bold rounded-md {{ $activeTab === 'rejected' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 dark:bg-gray-900 text-gray-500' }}">{{ __('Rejected Payments') }}</button>
                        <button wire:click="setTab('unattached')" class="px-4 py-1.5 text-xs font-bold rounded-md {{ $activeTab === 'unattached' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 dark:bg-gray-900 text-gray-500' }}">{{ __('Unattached Payments') }}</button>
                    </div>

                    @if(in_array($activeTab, ['unpaid', 'partial']))
                        <!-- Filters -->
                        <div class="mb-6 flex flex-wrap gap-3">
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search client, phone, email, product...') }}" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm flex-1 min-w-[240px]">

                            @if($activeTab === 'unpaid')
                                <select wire:model.live="statusFilter" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                    <option value="">{{ __('All statuses') }}</option>
                                    <option value="impaye">{{ __('Unpaid') }}</option>
                                    <option value="partiel">{{ __('Partial Payment') }}</option>
                                    <option value="en_attente_verification">{{ __('Pending Verification') }}</option>
                                    <option value="en_retard">{{ __('Overdue') }}</option>
                                </select>
                            @endif
                        </div>

                        @php
                            $statusBadge = fn ($status) => match($status) {
                                'impaye' => ['bg-red-100 text-red-700', __('Unpaid')],
                                'partiel' => ['bg-amber-100 text-amber-700', __('Partial Payment')],
                                'en_attente_verification' => ['bg-blue-100 text-blue-700', __('Pending Verification')],
                                'en_retard' => ['bg-rose-200 text-rose-800', __('Overdue')],
                                default => ['bg-gray-100 text-gray-600', $status],
                            };
                        @endphp

                        <!-- Desktop table -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">{{ __('Client') }}</th>
                                        <th scope="col" class="px-4 py-3">{{ __('Contact') }}</th>
                                        <th scope="col" class="px-4 py-3">{{ __('Order / Product') }}</th>
                                        <th scope="col" class="px-4 py-3 text-right">{{ __('Total') }}</th>
                                        <th scope="col" class="px-4 py-3 text-right">{{ __('Paid') }}</th>
                                        <th scope="col" class="px-4 py-3 text-right">{{ __('Remaining') }}</th>
                                        <th scope="col" class="px-4 py-3">{{ __('Last Payment') }}</th>
                                        <th scope="col" class="px-4 py-3">{{ __('Order Date') }}</th>
                                        <th scope="col" class="px-4 py-3">{{ __('Status') }}</th>
                                        <th scope="col" class="px-4 py-3">{{ __('Last Note') }}</th>
                                        <th scope="col" class="px-4 py-3">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rows as $row)
                                        @php $order = $row['order']; $badge = $statusBadge($row['status']); @endphp
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-4 py-4 font-medium text-gray-900 dark:text-white">{{ $order->client->name }}</td>
                                            <td class="px-4 py-4 text-xs">
                                                <div>{{ $order->client->phone ?: '—' }}</div>
                                                <div class="text-gray-400">{{ $order->client->email ?: '—' }}</div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div>{{ $order->product->name }}</div>
                                                <div class="text-[10px] text-gray-400 font-mono">#{{ $order->id }}</div>
                                            </td>
                                            <td class="px-4 py-4 text-right">{{ $order->client->formatAmount($order->price) }}</td>
                                            <td class="px-4 py-4 text-right text-emerald-600 font-bold">{{ $order->client->formatAmount($order->paid_amount) }}</td>
                                            <td class="px-4 py-4 text-right text-rose-600 font-bold">{{ $order->client->formatAmount($order->pending_amount) }}</td>
                                            <td class="px-4 py-4 text-xs">{{ $row['last_payment']?->payment_date?->format('d M Y') ?? '—' }}</td>
                                            <td class="px-4 py-4 text-xs">{{ $order->purchase_date->format('d M Y') }}</td>
                                            <td class="px-4 py-4">
                                                <span class="text-xs font-bold px-2 py-0.5 rounded {{ $badge[0] }}">{{ $badge[1] }}</span>
                                            </td>
                                            <td class="px-4 py-4 text-xs max-w-[160px] truncate" title="{{ $row['last_note'] }}">{{ $row['last_note'] ?: '—' }}</td>
                                            <td class="px-4 py-4">
                                                <div class="flex flex-wrap gap-2">
                                                    <a href="{{ route('clients.show', $order->client_id) }}" class="text-xs font-bold text-indigo-600 uppercase">{{ __('Profile') }}</a>
                                                    <a href="{{ route('orders.edit', $order) }}" class="text-xs font-bold text-blue-600 uppercase">{{ __('Order') }}</a>
                                                    <a href="{{ route('payments.create', ['client_id' => $order->client_id, 'order_id' => $order->id]) }}" class="text-xs font-bold text-emerald-600 uppercase">{{ __('Add Payment') }}</a>
                                                    <a href="{{ route('clients.show', ['client' => $order->client_id, 'tab' => 'notes']) }}" class="text-xs font-bold text-gray-500 uppercase">{{ __('Add Note') }}</a>
                                                    @if($order->client->whatsapp_url)
                                                        <a href="{{ $order->client->whatsapp_url }}" target="_blank" class="text-xs font-bold text-emerald-500 uppercase">{{ __('WhatsApp') }}</a>
                                                    @endif
                                                    @if($row['proof'])
                                                        <a href="{{ Storage::url($row['proof']->file_path) }}" target="_blank" class="text-xs font-bold text-purple-600 uppercase">{{ __('View Proof') }}</a>
                                                    @endif
                                                    @if($row['pending_payment'])
                                                        <button wire:click="confirmPayment({{ $row['pending_payment']->id }})" wire:confirm="{{ __('Confirm this payment was received?') }}" class="text-xs font-bold text-emerald-700 uppercase">{{ __('Validate') }}</button>
                                                        <button wire:click="rejectPayment({{ $row['pending_payment']->id }})" wire:confirm="{{ __('Reject this pending payment?') }}" class="text-xs font-bold text-red-600 uppercase">{{ __('Reject') }}</button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="px-4 py-8 text-center text-gray-500">{{ __('No matching orders found.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile cards -->
                        <div class="md:hidden space-y-3">
                            @forelse($rows as $row)
                                @php $order = $row['order']; $badge = $statusBadge($row['status']); @endphp
                                <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-white dark:bg-gray-900">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $order->client->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $order->product->name }}</div>
                                        </div>
                                        <span class="text-xs font-bold px-2 py-0.5 rounded {{ $badge[0] }}">{{ $badge[1] }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 mb-1">{{ $order->client->phone ?: '—' }} &middot; {{ $order->client->email ?: '—' }}</div>
                                    <div class="grid grid-cols-3 gap-2 text-xs my-3">
                                        <div><div class="text-gray-400">{{ __('Total') }}</div><div class="font-bold">{{ $order->client->formatAmount($order->price) }}</div></div>
                                        <div><div class="text-gray-400">{{ __('Paid') }}</div><div class="font-bold text-emerald-600">{{ $order->client->formatAmount($order->paid_amount) }}</div></div>
                                        <div><div class="text-gray-400">{{ __('Remaining') }}</div><div class="font-bold text-rose-600">{{ $order->client->formatAmount($order->pending_amount) }}</div></div>
                                    </div>
                                    @if($row['last_note'])
                                        <div class="text-xs italic text-gray-400 mb-2">"{{ $row['last_note'] }}"</div>
                                    @endif
                                    <div class="flex flex-wrap gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                                        <a href="{{ route('clients.show', $order->client_id) }}" class="text-xs font-bold text-indigo-600 uppercase">{{ __('Profile') }}</a>
                                        <a href="{{ route('orders.edit', $order) }}" class="text-xs font-bold text-blue-600 uppercase">{{ __('Order') }}</a>
                                        <a href="{{ route('payments.create', ['client_id' => $order->client_id, 'order_id' => $order->id]) }}" class="text-xs font-bold text-emerald-600 uppercase">{{ __('Add Payment') }}</a>
                                        @if($order->client->whatsapp_url)
                                            <a href="{{ $order->client->whatsapp_url }}" target="_blank" class="text-xs font-bold text-emerald-500 uppercase">{{ __('WhatsApp') }}</a>
                                        @endif
                                        @if($row['pending_payment'])
                                            <button wire:click="confirmPayment({{ $row['pending_payment']->id }})" wire:confirm="{{ __('Confirm this payment was received?') }}" class="text-xs font-bold text-emerald-700 uppercase">{{ __('Validate') }}</button>
                                            <button wire:click="rejectPayment({{ $row['pending_payment']->id }})" wire:confirm="{{ __('Reject this pending payment?') }}" class="text-xs font-bold text-red-600 uppercase">{{ __('Reject') }}</button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-gray-500">{{ __('No matching orders found.') }}</div>
                            @endforelse
                        </div>

                        <div class="mt-4">{{ $rows->links() }}</div>

                    @elseif($activeTab === 'rejected')
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th class="px-4 py-3">{{ __('Date') }}</th>
                                        <th class="px-4 py-3">{{ __('Client') }}</th>
                                        <th class="px-4 py-3">{{ __('Order') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Amount') }}</th>
                                        <th class="px-4 py-3">{{ __('Rejected By') }}</th>
                                        <th class="px-4 py-3">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                            <td class="px-4 py-4 whitespace-nowrap">{{ $payment->rejected_at?->format('d M Y H:i') }}</td>
                                            <td class="px-4 py-4 font-medium text-gray-900 dark:text-white">{{ $payment->client->name ?? __('Unattached') }}</td>
                                            <td class="px-4 py-4">{{ $payment->order?->product?->name ?? '—' }}</td>
                                            <td class="px-4 py-4 text-right font-bold">{{ $payment->formatAmount($payment->amount) }}</td>
                                            <td class="px-4 py-4 text-xs">{{ $payment->rejectedBy?->name ?? '—' }}</td>
                                            <td class="px-4 py-4">
                                                <div class="flex gap-3">
                                                    <a href="{{ route('payments.edit', $payment) }}" class="text-xs font-bold text-blue-600 uppercase">{{ __('View') }}</a>
                                                    @if($payment->proofs->first())
                                                        <a href="{{ Storage::url($payment->proofs->first()->file_path) }}" target="_blank" class="text-xs font-bold text-purple-600 uppercase">{{ __('View Proof') }}</a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No rejected payments.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $payments->links() }}</div>

                    @elseif($activeTab === 'unattached')
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th class="px-4 py-3">{{ __('Date') }}</th>
                                        <th class="px-4 py-3">{{ __('Reference') }}</th>
                                        <th class="px-4 py-3">{{ __('Method') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Amount') }}</th>
                                        <th class="px-4 py-3">{{ __('Status') }}</th>
                                        <th class="px-4 py-3">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                            <td class="px-4 py-4 whitespace-nowrap">{{ $payment->payment_date?->format('d M Y') }}</td>
                                            <td class="px-4 py-4 text-xs">{{ $payment->reference ?: '—' }}</td>
                                            <td class="px-4 py-4">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                            <td class="px-4 py-4 text-right font-bold">{{ $payment->formatAmount($payment->amount) }}</td>
                                            <td class="px-4 py-4"><span class="text-xs font-bold px-2 py-0.5 rounded bg-gray-100 text-gray-600">{{ __($payment->status) }}</span></td>
                                            <td class="px-4 py-4">
                                                <a href="{{ route('payments.edit', $payment) }}" class="text-xs font-bold text-blue-600 uppercase">{{ __('Attach to a Client') }}</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No unattached payments.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $payments->links() }}</div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
