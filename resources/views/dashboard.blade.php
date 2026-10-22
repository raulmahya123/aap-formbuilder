<x-app-layout>
    <x-slot name="header">
        <x-ui.section-title class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </x-ui.section-title>
    </x-slot>

    <div class="py-8">
        <div class="w-full min-w-0">
            <div class="bg-white overflow-hidden shadow-none sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
