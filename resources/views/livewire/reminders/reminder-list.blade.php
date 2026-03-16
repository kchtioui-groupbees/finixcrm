<div>
    @if (session()->has('reminder_message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('reminder_message') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    Actionable Reminders
                </h3>
                @if($reminders->count() > 0)
                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300">
                        {{ $reminders->count() }} Pending
                    </span>
                @endif
            </div>

            @if($reminders->count() === 0)
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p>All caught up! No pending reminders.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($reminders as $reminder)
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 border border-gray-100 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-white dark:hover:bg-gray-700 transition-colors">
                            <div class="mb-3 sm:mb-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $reminder->client ? $reminder->client->name : 'Unknown Client' }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                    @if($reminder->type === 'upcoming_expiry')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 mr-2">Expiring Soon</span>
                                    @elseif($reminder->type === 'expired')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 mr-2">Expired</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 mr-2">Custom</span>
                                    @endif
                                    @if($reminder->order)
                                        <a href="{{ route('orders.edit', $reminder->order_id) }}" class="hover:underline text-indigo-600 dark:text-indigo-400">{{ $reminder->order->product }}</a>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Triggered: {{ \Carbon\Carbon::parse($reminder->trigger_date)->format('M d, Y') }}
                                </p>
                            </div>
                            <div class="flex gap-2 w-full sm:w-auto">
                                <button wire:click="markAsCompleted({{ $reminder->id }})" class="flex-1 sm:flex-none px-3 py-1.5 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors text-center w-full">
                                    Complete
                                </button>
                                <button wire:click="dismiss({{ $reminder->id }})" class="flex-1 sm:flex-none px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors text-center w-full">
                                    Dismiss
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
