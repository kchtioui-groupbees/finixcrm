<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex justify-between items-center">
            <span>{{ __('Payment Methods') }}</span>
            <a href="{{ route('payments.methods.create') }}" class="btn-phoenix">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ __('New Payment Method') }}
            </a>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex bg-gray-100 dark:bg-gray-900 p-1 rounded-lg">
                            <a href="{{ route('payments.index') }}" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500">{{ __('All Payments') }}</a>
                            <a href="{{ route('payments.pending') }}" class="px-4 py-1.5 text-xs font-bold rounded-md text-gray-500">{{ __('Pending Confirmation') }}</a>
                            <a href="{{ route('payments.methods') }}" class="px-4 py-1.5 text-xs font-bold rounded-md bg-white dark:bg-gray-700 shadow-sm">{{ __('Payment Methods') }}</a>
                        </div>
                        <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-500">
                            <input type="checkbox" wire:model.live="showArchived" class="rounded border-gray-300 text-indigo-600">
                            {{ __('Show archived') }}
                        </label>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">{{ __('Method') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Category') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Fee') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Confirmation') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Active') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Public') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($methods as $method)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700" wire:key="method-{{ $method->id }}">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                @if($method->logo_path)
                                                    <img src="{{ Storage::url($method->logo_path) }}" class="h-8 w-8 rounded-lg object-cover">
                                                @endif
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-white">{{ $method->label }}</div>
                                                    <div class="text-xs text-gray-400">{{ $method->key }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-xs">{{ $method->category ? \App\Models\PaymentMethod::CATEGORIES[$method->category] ?? $method->category : '—' }}</td>
                                        <td class="px-6 py-4 text-xs max-w-[200px]">{{ $method->fee_summary }}</td>
                                        <td class="px-6 py-4">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:click="toggleConfirmation({{ $method->id }})" @checked($method->requires_confirmation) class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                            </label>
                                        </td>
                                        <td class="px-6 py-4">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:click="toggleActive({{ $method->id }})" @checked($method->is_active) @disabled($method->isArchived()) class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                            </label>
                                        </td>
                                        <td class="px-6 py-4 text-xs">
                                            @if($method->is_public)
                                                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-bold">{{ __('Public') }}</span>
                                            @else
                                                <span class="text-gray-400">{{ __('Hidden') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex gap-3 items-center flex-wrap">
                                                <a href="{{ route('payments.methods.edit', $method) }}" class="text-xs font-bold text-blue-600 hover:underline">{{ __('Edit') }}</a>
                                                <button type="button" wire:click="duplicate({{ $method->id }})" class="text-xs font-bold text-indigo-600 hover:underline">{{ __('Duplicate') }}</button>
                                                @if($method->isArchived())
                                                    <button type="button" wire:click="unarchive({{ $method->id }})" class="text-xs font-bold text-emerald-600 hover:underline">{{ __('Unarchive') }}</button>
                                                @else
                                                    <button type="button" wire:click="archive({{ $method->id }})" wire:confirm="{{ __('Archive this payment method? It will stop appearing on the public page and can be restored later.') }}" class="text-xs font-bold text-red-500 hover:underline">{{ __('Archive') }}</button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">{{ $showArchived ? __('No archived payment methods.') : __('No payment methods.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
