<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Warranty Claims Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if (session()->has('message'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                            {{ session('message') }}
                        </div>
                    @endif

                    <div class="flex flex-col md:flex-row justify-between mb-6 gap-4">
                        <div class="flex-1">
                            <input wire:model.live="search" type="text" placeholder="{{ __('Search by client or subject...') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500">
                        </div>
                        <div>
                            <select wire:model.live="status" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="open">{{ __('Open') }}</option>
                                <option value="processing">{{ __('Processing') }}</option>
                                <option value="approved">{{ __('Approved') }}</option>
                                <option value="rejected">{{ __('Rejected') }}</option>
                                <option value="resolved">{{ __('Resolved') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">{{ __('Date') }}</th>
                                    <th class="px-6 py-3">{{ __('Client & Product') }}</th>
                                    <th class="px-6 py-3">{{ __('Subject') }}</th>
                                    <th class="px-6 py-3">{{ __('Status') }}</th>
                                    <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @if(count($claims) > 0)
                                    @foreach($claims as $claim)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $claim->created_at->format('d M Y') }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="font-bold">{{ $claim->order->client->name ?? __('Unknown') }}</div>
                                                <div class="text-xs text-gray-500">{{ $claim->order->product->name ?? __('Unknown Order') }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="font-medium">{{ $claim->subject }}</div>
                                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ $claim->description }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                 <select wire:change="updateStatus({{ $claim->id }}, $event.target.value)" class="text-xs rounded-full px-3 py-1 border-none font-bold uppercase tracking-wider
                                                    {{ $claim->status === 'open' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $claim->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $claim->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ $claim->status === 'resolved' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $claim->status === 'processing' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                                ">
                                                    <option value="open" {{ $claim->status === 'open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                                                    <option value="processing" {{ $claim->status === 'processing' ? 'selected' : '' }}>{{ __('Processing') }}</option>
                                                    <option value="approved" {{ $claim->status === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                                                    <option value="rejected" {{ $claim->status === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                                                    <option value="resolved" {{ $claim->status === 'resolved' ? 'selected' : '' }}>{{ __('Resolved') }}</option>
                                                </select>
                                            </td>
                                            <td class="px-6 py-4 text-right flex justify-end gap-2">
                                                <button onclick="confirm('{{ __('Are you sure?') }}') || event.stopImmediatePropagation()" wire:click="deleteClaim({{ $claim->id }})" class="text-red-600 hover:text-red-900">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 italic">{{ __('No claims found') }}.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $claims->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
