@section('content')
<div class="max-w-xl mx-auto mt-10 p-6 rounded-2xl border bg-white dark:bg-slate-900">
  <div class="flex items-start gap-3">
    <div class="shrink-0 w-10 h-10 rounded-full bg-amber-100 text-amber-700 grid place-content-center">⚠️</div>
    <div>
      <h1 class="text-lg font-semibold mb-1">Akses Terbatas</h1>
      <p class="text-sm text-slate-600 dark:text-slate-300">
        Maaf, Anda tidak memiliki hak untuk mengakses bagian ini.
        @if(!empty($reason))
          <br><span class="italic">{{ $reason }}</span>
        @endif
      </p>

      @isset($backUrl)
        <a href="{{ $backUrl }}" class="inline-block mt-4 px-3 py-2 rounded border text-sm">Kembali</a>
      @else
        <a href="{{ route('admin.dashboard') }}" class="inline-block mt-4 px-3 py-2 rounded border text-sm">Ke Dashboard</a>
      @endisset
    </div>
  </div>
</div>
@endsection