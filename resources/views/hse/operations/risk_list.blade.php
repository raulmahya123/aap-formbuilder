@extends('layouts.app')

@section('title', 'Risk Register List — MANDALA')

@section('content')
<div class="ui-page space-y-6" x-data="{ openModal: false, selectedRisk: null }">
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
            <button type="button" @click="openModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-[#1646A0] px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#123880] transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Risk Assessment
            </button>
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
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="12" title="Belum ada data Risk Assessment" message="Silakan tambahkan data asesmen baru atau ubah filter pencarian." />
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($risks->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $risks->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL TAMBAH RISK ASSESSMENT --}}
    <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-transition>
        <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-2xl p-6" @click.away="openModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900">Tambah Form Risk Assessment Baru</h3>
                <button type="button" @click="openModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('hse.dashboard.risk.assessment.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Site *</label>
                        <select name="site_id" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            @foreach($options['sites'] ?? [] as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Department</label>
                        <select name="department_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="">-- Pilih Department --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Aktivitas Pekerjaan *</label>
                    <input type="text" name="activity" required placeholder="Contoh: Pengoperasian Hauling Truck Malam Hari" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Bahaya (Hazard) *</label>
                        <input type="text" name="hazard" required placeholder="Contoh: Tabrakan unit akibat blindspot & fatik" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Bahaya *</label>
                        <select name="category" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="Operational">Operational</option>
                            <option value="Mechanical">Mechanical</option>
                            <option value="Electrical">Electrical</option>
                            <option value="Geotechnical">Geotechnical</option>
                            <option value="Chemical">Chemical</option>
                            <option value="Environmental">Environmental</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Existing Controls (Kontrol Saat Ini)</label>
                    <textarea name="existing_controls" rows="2" placeholder="Kontrol yang sudah ada..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Inherent Likelihood (1-5) *</label>
                        <select name="likelihood" required class="w-full text-xs rounded-lg border-slate-300">
                            <option value="1">1 - Rare</option>
                            <option value="2">2 - Unlikely</option>
                            <option value="3" selected>3 - Possible</option>
                            <option value="4">4 - Likely</option>
                            <option value="5">5 - Almost Certain</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Inherent Consequence (1-5) *</label>
                        <select name="consequence" required class="w-full text-xs rounded-lg border-slate-300">
                            <option value="1">1 - Insignificant</option>
                            <option value="2">2 - Minor</option>
                            <option value="3" selected>3 - Moderate</option>
                            <option value="4">4 - Major</option>
                            <option value="5">5 - Catastrophic</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Residual Likelihood (Sesudah Kontrol)</label>
                        <select name="residual_likelihood" class="w-full text-xs rounded-lg border-slate-300">
                            <option value="1">1 - Rare</option>
                            <option value="2" selected>2 - Unlikely</option>
                            <option value="3">3 - Possible</option>
                            <option value="4">4 - Likely</option>
                            <option value="5">5 - Almost Certain</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Residual Consequence</label>
                        <select name="residual_consequence" class="w-full text-xs rounded-lg border-slate-300">
                            <option value="1">1 - Insignificant</option>
                            <option value="2" selected>2 - Minor</option>
                            <option value="3">3 - Moderate</option>
                            <option value="4">4 - Major</option>
                            <option value="5">5 - Catastrophic</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Rencana Mitigasi (Treatment)</label>
                        <input type="text" name="treatment" placeholder="Tindakan mitigasi tambahan..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Due Date Mitigasi</label>
                        <input type="date" name="treatment_due_date" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Penanggung Jawab (PIC)</label>
                    <select name="owner_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                        <option value="">-- Pilih PIC --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="openModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-[#1646A0] hover:bg-[#123880] rounded-lg shadow-sm">Simpan Risk Assessment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
