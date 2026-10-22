@extends('layouts.app')
@section('title','Input Harian')
@section('content')
<x-ui.page>
<x-ui.page-header><div class="ui-page-header__copy"><x-ui.page-title>Input Harian</x-ui.page-title><x-ui.page-description>Data indikator harian berdasarkan site dan periode.</x-ui.page-description></div><x-ui.page-actions><x-ui.link icon="plus" variant="primary" :href="route('admin.daily.create')">Input Data</x-ui.link></x-ui.page-actions>
<x-slot:filters>
<x-ui.table-toolbar><x-ui.table-filter name="site_id" :value="$siteId" :options="$sites->pluck('name','id')" label="Site" placeholder="Semua site yang diizinkan" /><label for="daily-month">Bulan</label><x-ui.form-select id="daily-month" name="month">@foreach(range(1,12) as $m)<option value="{{ $m }}" @selected($month === $m)>{{ \Carbon\Carbon::create(2000,$m,1)->translatedFormat('F') }}</option>@endforeach</x-ui.form-select><label for="daily-year">Tahun</label><x-ui.form-input id="daily-year" type="number" name="year" :value="$year" min="2000" max="2100" required />
<x-slot:actions>
<x-ui.button variant="secondary" type="submit">Terapkan</x-ui.button><x-ui.link variant="secondary" :href="route('admin.daily.index')">Reset</x-ui.link>
</x-slot:actions>
</x-ui.table-toolbar>
</x-slot:filters>
</x-ui.page-header>
<x-ui.table :paginator="$rows"><thead><tr><th>Tanggal</th><th>Site</th><th>Indikator</th><th>Shift</th><th class="text-right">Nilai</th><th>Catatan</th><th>Status input</th></tr></thead><tbody>@forelse($rows as $row)<tr><td>{{ $row->date->format('d M Y') }}</td><td>{{ $row->site?->code ?? '—' }}</td><td>{{ $row->indicator?->name ?? '—' }}</td><td>{{ $row->shift }}</td><td class="text-right">{{ number_format((float) $row->value, 2, ',', '.') }}</td><td>{{ $row->note ?: '—' }}</td><td><x-ui.status-badge :variant="$row->is_late ? 'warning' : 'success'">{{ $row->is_late ? 'Terlambat' : 'Tepat waktu' }}</x-ui.status-badge></td></tr>@empty<x-ui.table-empty-state colspan="7" />@endforelse</tbody></x-ui.table>
</x-ui.page>
@endsection
