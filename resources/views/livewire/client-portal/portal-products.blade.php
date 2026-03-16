@php
    /** @var \App\Models\Order[]|\Illuminate\Database\Eloquent\Collection $orders */
@endphp
<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Products') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-xl font-bold mb-6">{{ __('Your Subscriptions & Services') }}</h3>

                    @if(count($orders) === 0)
                        <p class="text-gray-500 italic">{{ __('No products found for your account') }}.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">{{ __('Product Name') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Dates & Duration') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Status') }}</th>
                                        <th scope="col" class="px-6 py-3">{{ __('Finances') }}</th>
                                        <th scope="col" class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                                {{ $order->product->name ?? __('Unknown') }}
                                            </td>
                                            <td class="px-6 py-4 space-y-1">
                                                <p><span class="text-gray-400">{{ __('Purchased') }}:</span> {{ \Carbon\Carbon::parse($order->purchase_date)->format('M d, Y') }}</p>
                                                <p><span class="text-gray-400">{{ __('Expires') }}:</span> <strong class="text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($order->expiry_date)->format('M d, Y') }}</strong></p>
                                                @if($order->duration)
                                                    <p class="text-xs text-gray-400">{{ __('Duration') }}: {{ $order->duration }}</p>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $status = $order->dynamic_status;
                                                    $bgClass = 'bg-gray-100 text-gray-800';
                                                    if ($status === 'Active') $bgClass = 'bg-green-100 text-green-800';
                                                    if ($status === 'Expiring Soon') $bgClass = 'bg-orange-100 text-orange-800';
                                                    if ($status === 'Expired') $bgClass = 'bg-red-100 text-red-800';
                                                    if ($status === 'Payment Pending') $bgClass = 'bg-yellow-100 text-yellow-800';
                                                @endphp
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $bgClass }}">
                                                    {{ __($status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 space-y-1">
                                                <p><span class="text-gray-400">{{ __('Total Price') }}:</span> {{ $client->formatAmount($order->price) }}</p>
                                                <p><span class="text-gray-400">{{ __('Paid') }}:</span> <span class="text-green-600">{{ $client->formatAmount($order->paid_amount) }}</span></p>
                                                @if($order->pending_amount > 0)
                                                    <p class="text-red-500 font-semibold text-xs border border-red-200 bg-red-50 dark:bg-red-900/20 px-1 py-0.5 rounded inline-block mt-1">{{ __('Pending') }}: {{ $client->formatAmount($order->pending_amount) }}</p>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <a href="{{ route('client.products.show', $order->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-100 rounded-md hover:bg-indigo-200 transition-colors">
                                                    {{ __('View Details') }}
                                                </a>
                                            </td>
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

</div>
