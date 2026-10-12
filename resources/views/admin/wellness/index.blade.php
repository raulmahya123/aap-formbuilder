@extends('layouts.app')

@section('title', 'Wellness Dashboard')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
    <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-maroon-700">People & Wellbeing</p><h1 class="mt-1 text-3xl font-serif">Wellness Dashboard</h1><p class="mt-2 text-sm text-coal-500">Ringkasan check-in wellness yang masuk dari form publik.</p></div>
    <a href="{{ route('wellness.create') }}" target="_blank" class="rounded-xl border border-maroon-600 px-4 py-2 text-sm font-medium text-maroon-700 hover:bg-maroon-50">Buka Form Publik ↗</a>
  </div>

  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([['total','Total Respons','🌿'],['today','Masuk Hari Ini','📅'],['good_mood','Mood Baik','😊'],['high_stress','Stres Tinggi (4–5)','⚠️']] as [$key,$label,$icon])
      <div class="rounded-2xl border border-coal-100 bg-white p-5 shadow-soft"><div class="flex items-center justify-between"><span class="text-sm text-coal-500">{{ $label }}</span><span class="text-xl">{{ $icon }}</span></div><div class="mt-3 text-3xl font-semibold">{{ number_format($stats[$key], 0, ',', '.') }}</div></div>
    @endforeach
  </div>

  <form method="GET" class="flex flex-col gap-3 rounded-2xl border border-coal-100 bg-white p-4 shadow-soft sm:flex-row">
    <input name="department" value="{{ request('department') }}" class="rounded-xl border-slate-200 px-4 py-2 text-sm" placeholder="Cari departemen">
    <select name="mood" class="rounded-xl border-slate-200 px-4 py-2 text-sm"><option value="">Semua mood</option>@foreach(['baik'=>'Baik','cukup'=>'Cukup','kurang'=>'Kurang'] as $value=>$label)<option value="{{ $value }}" @selected(request('mood') === $value)>{{ $label }}</option>@endforeach</select>
    <button class="rounded-xl bg-coal-800 px-4 py-2 text-sm font-medium text-white">Filter</button>
    <a href="{{ route('admin.wellness.index') }}" class="rounded-xl border px-4 py-2 text-center text-sm">Reset</a>
  </form>

  <div class="overflow-hidden rounded-2xl border border-coal-100 bg-white shadow-soft">
    <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-ivory-100 text-xs uppercase tracking-wider text-coal-500"><tr><th class="px-5 py-4">Waktu</th><th class="px-5 py-4">Nama</th><th class="px-5 py-4">Unit</th><th class="px-5 py-4">Mood</th><th class="px-5 py-4">Stres</th><th class="px-5 py-4">Tidur</th><th class="px-5 py-4">Catatan</th></tr></thead><tbody class="divide-y divide-coal-100">@forelse($responses as $response)<tr class="hover:bg-ivory-50"><td class="whitespace-nowrap px-5 py-4 text-coal-500">{{ $response->created_at->format('d M Y H:i') }}</td><td class="px-5 py-4"><div class="font-medium">{{ $response->name }}</div><div class="text-xs text-coal-400">{{ $response->email ?: '—' }}</div></td><td class="px-5 py-4">{{ $response->department ?: '—' }}</td><td class="px-5 py-4">{{ ['baik'=>'😊 Baik','cukup'=>'😐 Cukup','kurang'=>'😔 Kurang'][$response->mood] ?? $response->mood }}</td><td class="px-5 py-4">{{ $response->stress_level }}/5</td><td class="px-5 py-4">{{ $response->sleep_hours !== null ? $response->sleep_hours.' jam' : '—' }}</td><td class="max-w-xs px-5 py-4 text-coal-500">{{ $response->notes ?: '—' }}</td></tr>@empty<tr><td colspan="7" class="px-5 py-12 text-center text-coal-500">Belum ada respons wellness.</td></tr>@endforelse</tbody></table></div>
    @if($responses->hasPages())<div class="border-t border-coal-100 px-5 py-4">{{ $responses->links() }}</div>@endif
  </div>
</div>
@endsection
