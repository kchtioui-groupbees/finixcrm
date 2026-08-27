<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Payment Methods') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="mb-6 flex bg-gray-100 dark:bg-gray-900 p-1 rounded-lg w-fit">
                        <a href="{{ route('payments.index') }}" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500">{{ __('All Payments') }}</a>
                        <a href="{{ route('payments.pending') }}" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500">{{ __('Pending Confirmation') }}</a>
                        <a href="{{ route('payments.methods') }}" class="px-4 py-1.5 text-xs font-bold rounded-md bg-white dark:bg-gray-700 shadow-sm">{{ __('Payment Methods') }}</a>
                    </div>

                    <p class="text-sm text-gray-500 mb-6">{{ __('Toggle whether a method requires manual confirmation before it has any financial effect. This is configuration, not code — changes apply immediately to new payments.') }}</p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">{{ __('Method') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Key') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Requires Confirmation') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Active') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($methods as $method)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700" wire:key="method-{{ $method->id }}">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $method->label }}</td>
                                        <td class="px-6 py-4 text-xs text-gray-400">{{ $method->key }}</td>
                                        <td class="px-6 py-4">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:click="toggleConfirmation({{ $method->id }})" @checked($method->requires_confirmation) class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                            </label>
                                        </td>
                                        <td class="px-6 py-4">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:click="toggleActive({{ $method->id }})" @checked($method->is_active) class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                            </label>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">{{ __('Add a payment method') }}</h3>
                        <form wire:submit="addMethod" class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                            <div class="flex-1 w-full">
                                <x-input-label for="newLabel" :value="__('Label')" />
                                <x-text-input id="newLabel" type="text" class="mt-1 block w-full" wire:model="newLabel" placeholder="{{ __('e.g. Postepay') }}" />
                                <x-input-error :messages="$errors->get('newLabel')" class="mt-2" />
                            </div>
                            <label class="inline-flex items-center gap-2 h-11">
                                <input type="checkbox" wire:model="newRequiresConfirmation" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Requires confirmation') }}</span>
                            </label>
                            <button type="submit" class="btn-phoenix h-11 px-6 text-xs">{{ __('Add') }}</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
