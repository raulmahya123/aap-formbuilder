<x-guest-layout>
<x-ui.page-header>
<div class="ui-page-header__copy"><x-ui.page-title>Verifikasi email</x-ui.page-title><x-ui.page-description>Periksa email Anda dan buka tautan verifikasi untuk mengaktifkan akun.</x-ui.page-description></div>
<x-ui.page-actions />
</x-ui.page-header>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <x-ui.button variant="secondary" type="submit" class="underline text-sm rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Log Out') }}
            </x-ui.button>
        </form>
    </div>
</x-guest-layout>
