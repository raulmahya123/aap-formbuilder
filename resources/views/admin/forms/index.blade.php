@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Form Tersedia</h1>

    {{-- Tombol tambah form --}}
    @if(Route::has('admin.forms.create'))
      @isset($department)
        {{-- Policy create(User $user, int $departmentId) --}}
        @can('create', [\App\Models\Form::class, $department->id])
          <a href="{{ route('admin.forms.create', ['department_id' => $department->id]) }}"
             class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">
            + Tambah Form
          </a>
        @endcan
      @else
        {{-- Fallback: policy create(User $user) --}}
        @can('create', \App\Models\Form::class)
          <a href="{{ route('admin.forms.create') }}"
             class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">
            + Tambah Form
          </a>
        @endcan
      @endisset
    @endif
  </div>

  <div class="space-y-3">
    @forelse($forms as $f)
      <div class="p-4 rounded border hover:bg-slate-50">
        <div class="flex items-start justify-between gap-3">
          <a class="flex-1" href="{{ route('front.forms.show', $f->slug) }}">
            <div class="font-medium">{{ $f->title }}</div>
            <div class="text-sm text-slate-500">
              {{ strtoupper($f->type) }} — {{ $f->department->name ?? 'Tanpa Departemen' }}
            </div>
          </a>

          {{-- Tampilkan tombol Builder hanya untuk form tipe "builder" dan user yang bisa update form --}}
          @if($f->type === 'builder')
            @can('update', $f)
              <a href="{{ route('admin.forms.builder', $f) }}"
                 class="text-xs px-2 py-1 rounded border bg-white hover:bg-slate-100">
                Builder
              </a>
            @endcan
          @endif
        </div>
      </div>
    @empty
      <div class="text-slate-500">Belum ada form.</div>
    @endforelse
  </div>
</div>
@endsection
