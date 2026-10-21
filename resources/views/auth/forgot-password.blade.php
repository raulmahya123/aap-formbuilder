<x-guest-layout>
<x-ui.page-header>
<div class="ui-page-header__copy"><x-ui.page-title>Lupa password</x-ui.page-title><x-ui.page-description>Masukkan email Anda untuk menerima tautan pengaturan ulang password.</x-ui.page-description></div>
<x-ui.page-actions />
</x-ui.page-header>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
