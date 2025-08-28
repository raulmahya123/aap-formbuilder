@extends('layouts.app')

@section('title', 'Daftar Formulir')

@section('content')
<div 
    x-data="{ dark: localStorage.getItem('theme')==='dark' }" 
    x-init="document.documentElement.classList.toggle('dark',dark)"
    class="bg-ivory-100 dark:bg-coal-900 min-h-screen text-coal-800 dark:text-ivory-100"
>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        <h1 class="text-2xl md:text-3xl font-serif mb-6">Daftar Formulir</h1>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($forms->count())
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($forms as $form)
                    <div class="p-5 rounded-2xl border border-coal-200 dark:border-coal-700 bg-white dark:bg-coal-800 shadow-soft flex flex-col justify-between">
                        <div>
                            <h2 class="text-lg font-semibold mb-2">{{ $form->title }}</h2>
                            <p class="text-sm text-coal-600 dark:text-coal-300 line-clamp-3">
                                {{ $form->description ?? 'Tanpa deskripsi' }}
                            </p>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('front.forms.show', $form) }}"
                               class="inline-block px-4 py-2 rounded-xl bg-maroon-700 text-white hover:bg-maroon-800 dark:bg-maroon-600 dark:hover:bg-maroon-700 transition">
                                Isi Formulir
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $forms->links() }}
            </div>
        @else
            <p class="text-coal-500 dark:text-coal-300">Belum ada formulir tersedia.</p>
        @endif
    </div>
</div>
@endsection
