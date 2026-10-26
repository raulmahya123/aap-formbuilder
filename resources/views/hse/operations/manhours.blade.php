@extends('layouts.app')

@section('title', 'SHE Manhours Management')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'Manhours Management',
        'subtitle' => 'Input, validasi SHE, riwayat, dan rekap jam kerja tervalidasi per site dan kontraktor.',
        'filters' => $filters,
        'options' => $options,
    ])

    @if(session('ok'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
            {{ session('ok') }}
        </div>
    @endif

    {{-- STATS CARDS --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Total Manhours Tervalidasi</span>
            <div class="mt-2 text-2xl font-bold text-[#1646A0]">{{ number_format($stats['total_hours'], 0, ',', '.') }} <span class="text-xs font-normal text-[#64748B]">jam</span></div>
        </div>
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Menunggu Validasi SHE</span>
            <div class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['pending_validation'] }} <span class="text-xs font-normal text-[#64748B]">record</span></div>
        </div>
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Total Riwayat Input</span>
            <div class="mt-2 text-2xl font-bold text-[#0F172A]">{{ $stats['total_records'] }} <span class="text-xs font-normal text-[#64748B]">entri</span></div>
        </div>
    </div>

    {{-- IMPORT EXCEL & BULK INPUT --}}
    <div class="rounded-xl border border-dashed border-[#94A3B8] bg-slate-50 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-[#0F172A]">Import Manhours via Excel / CSV</h3>
                <p class="mt-1 text-xs text-[#64748B]">Unggah file CSV/Excel dengan kolom: <code>Tanggal (YYYY-MM-DD), Jumlah Karyawan, Jam Kerja, Lost Hours</code>.</p>
            </div>
            <form action="{{ route('hse.dashboard.manhours.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                @csrf
                <input type="file" name="file" required accept=".csv,.xlsx,.xls" class="text-xs text-slate-500 file:mr-2 file:rounded-md file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-blue-700 cursor-pointer" />
                <button type="submit" class="rounded-lg bg-[#0F172A] px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                    Proses Import
                </button>
            </form>
        </div>
    </div>

    {{-- INPUT FORM MODAL / SECTION --}}
    <div class="rounded-xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-[#0F172A]">Input Jam Kerja (Manhours) Harian</h2>
        <p class="mt-1 text-xs text-[#64748B]">Input data jam kerja karyawan dan kontraktor. Hanya data berstatus 'Validated' yang berkontribusi ke KPI LTIFR & LTISR.</p>

        <form action="{{ route('hse.dashboard.manhours.store') }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-[#475569]">Tanggal</label>
                <input type="date" name="date" value="{{ now()->toDateString() }}" required class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Site</label>
                <select name="site_id" required class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]">
                    <option value="">Pilih Site</option>
                    @foreach($options['sites'] as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Perusahaan / Kontraktor</label>
                <select name="company_id" class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]">
                    <option value="">Internal / Pilih Perusahaan</option>
                    @foreach($options['companies'] as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Department</label>
                <select name="department_id" class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]">
                    <option value="">Pilih Department</option>
                    @foreach($options['departments'] as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Jumlah Karyawan (Manpower)</label>
                <input type="number" name="employee_count" min="0" value="0" required class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Jam Kerja (Worked Hours)</label>
                <input type="number" step="0.5" name="manhours" min="0" required placeholder="Contoh: 80" class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Lost Hours</label>
                <input type="number" step="0.5" name="lost_hours" min="0" value="0" class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full rounded-lg bg-[#1646A0] px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-[#123880] transition">
                    Simpan Jam Kerja
                </button>
            </div>
        </form>
    </div>

    {{-- TABEL RIWAYAT & VALIDASI --}}
    <x-hse.chart-card title="Antrean & Riwayat Manhours" subtitle="Validasi dan verifikasi resmi SHE Department">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Site</th>
                        <th>Perusahaan</th>
                        <th>Karyawan</th>
                        <th>Manhours</th>
                        <th>Status</th>
                        <th>Submitter</th>
                        <th>Validator</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>{{ $row->date->format('d M Y') }}</td>
                            <td><span class="font-medium text-[#0F172A]">{{ $row->site?->name ?? '—' }}</span></td>
                            <td>{{ $row->company?->name ?? 'Internal' }}</td>
                            <td>{{ number_format($row->employee_count) }}</td>
                            <td><strong>{{ number_format($row->manhours, 1) }}</strong> jam</td>
                            <td>
                                @if($row->status === 'Validated')
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">Validated</span>
                                @elseif($row->status === 'Submitted')
                                    <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Menunggu Validasi</span>
                                @else
                                    <span class="inline-flex rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-800">{{ $row->status }}</span>
                                @endif
                            </td>
                            <td>{{ $row->submitter?->name ?? '—' }}</td>
                            <td>{{ $row->validator?->name ?? '—' }}</td>
                            <td>
                                @if($row->status === 'Submitted' && auth()->user()?->isAdmin())
                                    <div class="flex items-center gap-1.5">
                                        <form action="{{ route('hse.dashboard.manhours.validate', $row->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="validate" />
                                            <button type="submit" class="rounded bg-emerald-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-emerald-700">Validasi</button>
                                        </form>
                                        <form action="{{ route('hse.dashboard.manhours.validate', $row->id) }}" method="POST" onsubmit="return confirm('Kembalikan entri manhours ini?')">
                                            @csrf
                                            <input type="hidden" name="action" value="return" />
                                            <button type="submit" class="rounded bg-rose-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-rose-700">Return</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-[#94A3B8]">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="9" title="Belum ada riwayat" message="Belum ada entri manhours yang dicatat." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>

    <div class="mt-4">
        {{ $records->links() }}
    </div>
</div>
@endsection
