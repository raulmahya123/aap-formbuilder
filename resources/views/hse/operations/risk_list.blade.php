@extends('layouts.app')

@section('title', 'Risk Register List — MANDALA')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'Risk Register List',
        'subtitle' => 'Daftar seluruh asesmen risiko operasional dengan detail Likelihood, Consequence, dan Kontrol.',
        'filters' => $filters,
        'options' => $options,
    ])

    {{-- Filter Toolbar & Actions --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('hse.dashboard.risk.list') }}" class="flex flex-wrap items-center gap-2">
            @if(request('likelihood') && request('consequence'))
                <input type="hidden" name="likelihood" value="{{ request('likelihood') }}">
                <input type="hidden" name="consequence" value="{{ request('consequence') }}">
                <span class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-1 text-xs font-semibold text-[#1646A0] border border-blue-200">
                    Filter Sel Matriks: L{{ request('likelihood') }} × C{{ request('consequence') }}
                    <a href="{{ route('hse.dashboard.risk.list') }}" class="text-rose-500 hover:text-rose-700 ml-1">&times;</a>
                </span>
            @endif

            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari aktivitas, hazard, kontrol..." class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0] focus:ring focus:ring-blue-100 w-56">

            <select name="level" class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0] focus:ring focus:ring-blue-100" onchange="this.form.submit()">
                <option value="">-- Semua Severity --</option>
                <option value="Low" {{ request('level') === 'Low' ? 'selected' : '' }}>Low (1-3)</option>
                <option value="Medium" {{ request('level') === 'Medium' ? 'selected' : '' }}>Medium (4-8)</option>
                <option value="High" {{ request('level') === 'High' ? 'selected' : '' }}>High (9-14)</option>
                <option value="Extreme" {{ request('level') === 'Extreme' ? 'selected' : '' }}>Extreme (15-25)</option>
            </select>

            <select name="status" class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0] focus:ring focus:ring-blue-100" onchange="this.form.submit()">
                <option value="">-- Status --</option>
                <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
            </select>

            <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700 transition">Filter</button>
            @if(request()->anyFilled(['q', 'level', 'status', 'likelihood']))
                <a href="{{ route('hse.dashboard.risk.list') }}" class="text-xs text-slate-500 hover:text-slate-800 py-2 px-1">Reset</a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <x-ui.link variant="primary" icon="plus" href="{{ route('hse.dashboard.risk.create') }}">Tambah Risk Assessment</x-ui.link>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Aktivitas (Activity)</th>
                        <th>Bahaya (Hazard)</th>
                        <th>Kategori</th>
                        <th>Site / Dept</th>
                        <th>Inherent (L×C)</th>
                        <th>Residual (L×C)</th>
                        <th>Severity</th>
                        <th>Existing Controls</th>
                        <th>Treatment</th>
                        <th>Status</th>
                        <th>PIC</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($risks as $r)
                        @php
                            $inherentColor = match($r->risk_level) {
                                'Extreme' => 'bg-rose-100 text-rose-800 border-rose-300',
                                'High' => 'bg-orange-100 text-orange-800 border-orange-300',
                                'Medium' => 'bg-amber-100 text-amber-800 border-amber-300',
                                default => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="font-bold text-slate-900">#{{ $r->id }}</td>
                            <td class="font-medium text-slate-900 max-w-xs">{{ $r->activity }}</td>
                            <td class="text-slate-600 max-w-xs">{{ $r->hazard }}</td>
                            <td><span class="inline-flex px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">{{ $r->category }}</span></td>
                            <td>
                                <div class="text-xs font-semibold text-slate-800">{{ $r->site?->code ?? $r->site?->name ?? '—' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $r->department?->name ?? '—' }}</div>
                            </td>
                            <td>
                                <span class="text-xs font-bold text-slate-700">L{{ $r->likelihood }} × C{{ $r->consequence }} = {{ $r->inherent_score }}</span>
                            </td>
                            <td>
                                <span class="text-xs font-bold text-slate-700">L{{ $r->residual_likelihood ?: $r->likelihood }} × C{{ $r->residual_consequence ?: $r->consequence }} = {{ $r->residual_score ?: $r->inherent_score }}</span>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $inherentColor }}">
                                    {{ $r->risk_level }}
                                </span>
                            </td>
                            <td class="text-xs text-slate-600 max-w-xs truncate" title="{{ $r->existing_controls }}">{{ $r->existing_controls ?: '—' }}</td>
                            <td class="text-xs text-slate-600 max-w-xs truncate" title="{{ $r->treatment }}">{{ $r->treatment ?: '—' }}</td>
                            <td><x-hse.status :value="$r->status" /></td>
                            <td class="text-xs font-medium text-slate-800">{{ $r->owner?->name ?? '—' }}</td>
                            <td>
                                <div class="flex items-center gap-1">
                                    @if($r->status !== 'Approved' && $r->status !== 'Archived')
                                        <form action="{{ route('hse.dashboard.risk.review', $r->id) }}" method="POST">
                                            @csrf
                                            <x-ui.icon-button action="edit" label="Review" type="submit" />
                                        </form>
                                        <form action="{{ route('hse.dashboard.risk.approve', $r->id) }}" method="POST">
                                            @csrf
                                            <x-ui.icon-button action="approve" label="Approve" type="submit" />
                                        </form>
                                    @endif
                                    @if($r->status !== 'Archived')
                                        <form action="{{ route('hse.dashboard.risk.close', $r->id) }}" method="POST" onsubmit="return confirm('Arsipkan asesmen risiko ini?')">
                                            @csrf
                                            <x-ui.icon-button action="deactivate" label="Archive" type="submit" />
                                        </form>
                                    @else
                                        <span class="text-[10px] text-slate-400">Archived</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="13" title="Belum ada data Risk Assessment" message="Silakan tambahkan data asesmen baru atau ubah filter pencarian." />
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ui-pagination-footer">
            {{ $risks->links() }}
        </div>
    </div>

</div>
@endsection
