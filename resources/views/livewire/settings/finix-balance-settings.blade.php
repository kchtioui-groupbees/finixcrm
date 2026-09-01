<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Finix Balance Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Controls whether a client\'s available Finix balance is automatically applied to their oldest unpaid orders. Applied balance is never counted as bank/cash revenue and never draws from cashback that is still pending.') }}
                    </p>

                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                        <div>
                            <div class="font-bold">{{ __('Automatic application') }}</div>
                            <div class="text-xs text-gray-500">{{ __('Turn the whole feature on or off.') }}</div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="autoApplyEnabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                        <div>
                            <div class="font-bold">{{ __('Apply to old unpaid orders') }}</div>
                            <div class="text-xs text-gray-500">{{ __('When on, available balance is swept to the oldest unpaid order first.') }}</div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="applyToOldUnpaidOrders" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <div>
                        <div class="font-bold mb-2">{{ __('Allowed credit types') }}</div>
                        <div class="text-xs text-gray-500 mb-3">{{ __('Which credit sources are eligible to be auto-applied.') }}</div>
                        <div class="space-y-2">
                            @foreach(\App\Services\FinixBalanceAutoApplyService::AUTO_APPLIABLE_TYPES as $type)
                                @php($label = \App\Services\FinixBalanceAutoApplyService::CREDIT_TYPE_LABELS[$type])
                                <label class="flex items-center gap-3">
                                    <input type="checkbox" wire:model="allowedTypes.{{ $type }}" class="rounded border-gray-300 text-emerald-600">
                                    <span>{{ __($label) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-3 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30 rounded-lg p-3 text-xs text-amber-700 dark:text-amber-500">
                            {{ __('Pending cashback is never eligible, whatever is ticked above. It is not a credit type at all: no balance entry exists until the related order is fully paid, so there is nothing to apply before then.') }}
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button wire:click="save" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded shadow">
                            {{ __('Save Settings') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
