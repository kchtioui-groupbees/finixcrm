<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $clientId ? __('Edit Client') : __('Create Client') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <form wire:submit="save">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" type="text" class="mt-1 block w-full" wire:model.live="name" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Real Email -->
                            <div>
                                <x-input-label for="email" :value="__('Real Email')" />
                                <x-text-input id="email" type="email" class="mt-1 block w-full" wire:model.blur="email" />
                                <p class="text-[10px] text-gray-400 mt-1 italic">{{ __('Optional. Leave empty and a system email will be generated below.') }}</p>
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <!-- Phone -->
                            <div>
                                <x-input-label for="phone" :value="__('Phone')" />
                                <x-text-input id="phone" type="text" class="mt-1 block w-full" wire:model="phone" />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>

                            <!-- Finix System Email -->
                            <div>
                                <x-input-label for="finix_email" :value="__('Finix System Email')" />
                                <x-text-input id="finix_email" type="email" class="mt-1 block w-full font-mono" wire:model="finix_email" placeholder="{{ __('auto-generated from name') }}" />
                                <p class="text-[10px] text-gray-400 mt-1 italic">{{ __('Used to log into the client portal. Auto-generated from the name, but you can edit it.') }}</p>
                                <x-input-error :messages="$errors->get('finix_email')" class="mt-2" />
                            </div>

                            @if(!$clientId)
                                <div class="md:col-span-2 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-lg p-4 text-sm text-indigo-700 dark:text-indigo-300">
                                    {{ __('This client will be created with the default temporary password. They will be required to change it on first login.') }}
                                </div>
                            @else
                                <div class="md:col-span-2 flex items-center gap-4">
                                    <button type="button" wire:click="generateTemporaryPassword" wire:confirm="{{ __('Reset this client\'s password to the default temporary one? They will have to change it on next login.') }}" class="text-sm font-bold text-amber-600 hover:underline">
                                        {{ __('Generate a new temporary password') }}
                                    </button>
                                    @if($temporaryPasswordJustGenerated)
                                        <span class="text-xs text-emerald-600 font-bold">{{ __('Done') }}</span>
                                    @endif
                                </div>
                            @endif

                            <!-- Currency -->
                            <div>
                                <x-input-label for="currency" :value="__('Default Currency')" />
                                <select id="currency" wire:model="currency" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="USD">USD ($)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="TND">TND (Tunisian Dinar)</option>
                                </select>
                                <x-input-error :messages="$errors->get('currency')" class="mt-2" />
                            </div>

                            <!-- Status -->
                            <div>
                                <x-input-label for="status" :value="__('Account Status')" />
                                <select id="status" wire:model="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="active">{{ __('Active') }}</option>
                                    <option value="inactive">{{ __('Inactive') }}</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Internal Notes')" />
                                <textarea id="notes" wire:model="notes" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="3"></textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>

                            <!-- Tags -->
                            <div class="md:col-span-2 text-gray-900 dark:text-gray-100">
                                <x-input-label for="tagInput" :value="__('Tags (e.g. VIP, Late Payer)')" />
                                <div class="flex mt-1">
                                    <x-text-input id="tagInput" type="text" class="block w-full rounded-r-none border-r-0" wire:model="tagInput" wire:keydown.enter.prevent="addTag" placeholder="{{ __('Add a tag...') }}" />
                                    <button type="button" wire:click="addTag" class="bg-gray-800 dark:bg-gray-200 border border-transparent rounded-r-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 py-2 px-4 inline-flex items-center">{{ __('Add') }}</button>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($tags as $index => $tag)
                                        <span class="inline-flex items-center px-2 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-300 rounded text-sm font-medium">
                                            {{ $tag }}
                                            <button type="button" wire:click="removeTag('{{ $tag }}')" class="ml-1 text-indigo-500 hover:text-indigo-700 focus:outline-none">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                            </button>
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                        </div>

                        <div class="mt-6 flex items-center justify-end gap-4">
                            <a href="{{ route('clients.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">{{ __('Cancel') }}</a>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="save"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="save">{{ $clientId ? __('Update Client') : __('Create Client') }}</span>
                                <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
