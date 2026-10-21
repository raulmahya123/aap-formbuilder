<x-guest-layout>
<x-ui.page-header>
<div class="ui-page-header__copy"><x-ui.page-title>Konfirmasi password</x-ui.page-title><x-ui.page-description>Masukkan password Anda untuk melanjutkan ke area ini.</x-ui.page-description></div>
<x-ui.page-actions />
</x-ui.page-header>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
