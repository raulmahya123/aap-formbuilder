@extends('layouts.app')

@section('title', 'HSE Incident & Near Miss')

@section('content')
@php $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []); @endphp
<div class="ui-page space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        @include('hse.dashboard.partials.header', [
            'title' => 'Incident & Near Miss Analytics',
            'subtitle' => 'Distribusi insiden, near miss, severity, aktivitas, waktu kejadian, investigasi, dan root cause.',
            'filters' => $filters,
            'options' => $options,
            'export' => 'incidents',
        ])
    </div>

    <div class="flex justify-end -mt-4">
        <button type="button" onclick="document.getElementById('modal-report-incident').showModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold shadow-sm hover:bg-[#0B2345] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Laporkan Insiden Baru
        </button>
    </div>

    @if(session('ok'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-800 font-medium">
            {{ session('ok') }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($data['kpis'] as $kpi)
            <x-hse.kpi-card :kpi="$kpi" />
        @endforeach
    </div>

    <x-hse.chart-card title="Tren Insiden & Near Miss" subtitle="Perbandingan bulanan dengan tahun sebelumnya">
        <x-hse.line-chart :chart="$data['charts']['trend']" />
    </x-hse.chart-card>

    <div class="grid gap-4 xl:grid-cols-3">
        <x-hse.chart-card title="Insiden per Tipe" subtitle="Distribusi tipe kejadian">
            <x-hse.donut-chart :chart="$data['charts']['by_type']" title="Tipe insiden" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Insiden per Severity" subtitle="Distribusi tingkat keparahan">
            <x-hse.donut-chart :chart="$data['charts']['by_severity']" title="Severity" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Status Investigasi" subtitle="Distribusi status investigasi">
            <x-hse.donut-chart :chart="$data['charts']['investigation']" title="Status investigasi" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Insiden per Department" subtitle="Top 10 department">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_department'])" label="Insiden per department" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Insiden per Site" subtitle="Top 10 site">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_site'])" label="Insiden per site" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Insiden per Aktivitas" subtitle="Top aktivitas saat kejadian">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_activity'])" label="Insiden per aktivitas" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Waktu Kejadian" subtitle="Distribusi per rentang jam">
            <x-ui.bar-chart :series="$toBars($data['charts']['time_of_day'])" label="Insiden per waktu" />
        </x-hse.chart-card>
    </div>

    <x-hse.chart-card title="Root Cause" subtitle="Klasifikasi root cause berdasarkan teks investigasi">
        <x-ui.bar-chart :series="$toBars($data['root_cause'])" label="Distribusi root cause" />
    </x-hse.chart-card>

    <x-hse.chart-card title="Daftar Insiden" subtitle="50 laporan terakhir pada periode">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>No Insiden</th>
                        <th>Tanggal</th>
                        <th>Site</th>
                        <th>Department</th>
                        <th>Kategori</th>
                        <th>Tipe</th>
                        <th>Severity</th>
                        <th>Investigasi</th>
                        <th>Investigator</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['rows'] as $row)
                        <tr>
                            <td>
                                <a href="{{ route('hse.dashboard.incidents.show', $row['id']) }}" class="font-bold text-[#1646A0] hover:underline">
                                    {{ $row['incident_no'] }}
                                </a>
                            </td>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['site'] }}</td>
                            <td>{{ $row['department'] }}</td>
                            <td>{{ $row['category'] }}</td>
                            <td>{{ $row['type'] }}</td>
                            <td><x-hse.status :value="$row['severity']" /></td>
                            <td><x-hse.status :value="$row['investigation_status']" /></td>
                            <td>{{ $row['investigator'] }}</td>
                            <td><x-hse.status :value="$row['status']" /></td>
                            <td>
                                <a href="{{ route('hse.dashboard.incidents.show', $row['id']) }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center gap-1">
                                    Detail &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="11" title="Belum ada insiden" message="Tidak ada laporan pada periode ini." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
</div>

<!-- Modal Laporkan Insiden Baru -->
<dialog id="modal-report-incident" class="rounded-xl border border-slate-200 p-0 shadow-2xl max-w-3xl w-full backdrop:bg-slate-900/50">
    <div class="p-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
            <h3 class="text-base font-bold text-[#0F172A]">Registrasi & Laporan Insiden Baru (Incident Register)</h3>
            <button type="button" onclick="document.getElementById('modal-report-incident').close()" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>

        <form action="{{ route('hse.dashboard.incidents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Site Operasional <span class="text-rose-500">*</span></label>
                    <select name="site_id" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="">-- Pilih Site --</option>
                        @foreach($options['sites'] ?? [] as $st)
                            <option value="{{ $st->id }}" {{ ($filters['site_id'] ?? null) == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen Terkait</label>
                    <select name="department_id" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($options['departments'] ?? [] as $dp)
                            <option value="{{ $dp->id }}" {{ ($filters['department_id'] ?? null) == $dp->id ? 'selected' : '' }}>{{ $dp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Waktu Kejadian (Tanggal & Jam) <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" name="occurred_at" required value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Spesifik / Area Kerja <span class="text-rose-500">*</span></label>
                    <input type="text" name="location" required placeholder="Contoh: Pit 1 West, Workshop B, Jalur Hauling KM 14" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Kejadian <span class="text-rose-500">*</span></label>
                    <select name="category" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="Incident">Incident</option>
                        <option value="Near Miss">Near Miss</option>
                        <option value="Occupational Illness">Occupational Illness (PAK)</option>
                        <option value="Environmental">Environmental</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Insiden (SHE Classification) <span class="text-rose-500">*</span></label>
                    <select name="type" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="Near Miss">Near Miss</option>
                        <option value="First Aid Case">First Aid Case (FAC)</option>
                        <option value="Medical Treatment Case">Medical Treatment Case (MTC)</option>
                        <option value="Restricted Work Case">Restricted Work Case (RWC)</option>
                        <option value="Lost Time Injury">Lost Time Injury (LTI)</option>
                        <option value="Fatality">Fatality</option>
                        <option value="Property Damage">Property Damage</option>
                        <option value="Environmental">Environmental Incident</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat Keparahan (Severity) <span class="text-rose-500">*</span></label>
                    <select name="severity" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Rinci Kejadian <span class="text-rose-500">*</span></label>
                <textarea name="description" rows="3" required placeholder="Jelaskan urutan kronologi kejadian, siapa yang terlibat, dan apa yang terjadi..." class="w-full text-xs rounded-lg border border-slate-300 p-2.5"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tindakan Langsung / Penanganan Pertama</label>
                    <textarea name="immediate_action" rows="2" placeholder="Tindakan cepat yang segera diambil di lokasi..." class="w-full text-xs rounded-lg border border-slate-300 p-2.5"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Dampak Lingkungan (Jika ada)</label>
                    <textarea name="environmental_impact" rows="2" placeholder="Tumpahan minyak, pencemaran air/udara, dsb..." class="w-full text-xs rounded-lg border border-slate-300 p-2.5"></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Korban</label>
                    <input type="text" name="injured_name" placeholder="Nama pekerja / kontraktor" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Cedera</label>
                    <input type="text" name="injury_type" placeholder="Luka gores, patah tulang, dll" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Hari Kerja Hilang</label>
                    <input type="number" name="lost_time_days" min="0" value="0" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Biaya Kerusakan (Rp)</label>
                    <input type="number" name="property_damage_cost" min="0" value="0" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Upload Bukti Foto / Dokumen Kejadian (Max 20MB)</label>
                <input type="file" name="evidence_file" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('modal-report-incident').close()" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold hover:bg-[#0B2345]">Submit Laporan Insiden</button>
            </div>
        </form>
    </div>
</dialog>
@endsection
