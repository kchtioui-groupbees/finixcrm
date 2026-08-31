<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('For security, please choose a new password before continuing. This is required the first time you log in.') }}
    </div>

    <form method="POST" action="{{ route('password.force-change') }}">
        @csrf

        <div>
            <x-input-label for="password" :value="__('New Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Set Password & Continue') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
