@extends('layouts.app')

@section('title', 'Detail Insiden: ' . $incident->incident_no)

@section('content')
<div class="ui-page space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('hse.dashboard.incidents') }}" class="text-xs font-medium text-slate-500 hover:text-[#1646A0]">
                    &larr; Kembali ke Incident Register
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-semibold text-slate-700">Detail Insiden</span>
            </div>
            <h1 class="text-2xl font-bold text-[#0F172A] mt-1 flex items-center gap-3">
                {{ $incident->incident_no }}
                <x-hse.status :value="$incident->status" />
                <x-hse.status :value="$incident->severity" />
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Site: <span class="font-semibold text-slate-700">{{ $incident->site?->name ?? '—' }}</span> &bull; 
                Departemen: <span class="font-semibold text-slate-700">{{ $incident->department?->name ?? '—' }}</span> &bull; 
                Waktu Kejadian: <span class="font-semibold text-slate-700">{{ $incident->occurred_at?->format('d M Y H:i') ?? '—' }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if(in_array($incident->status, ['Reported', 'Draft']))
                <button type="button" onclick="document.getElementById('modal-review').showModal()" class="px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700">
                    Review Insiden
                </button>
            @endif

            @if($incident->investigation_status !== 'Completed')
                <button type="button" onclick="document.getElementById('modal-investigate').showModal()" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
                    Input Hasil Investigasi
                </button>
            @endif

            @if($incident->status === 'Under Review' || ($incident->investigation_status === 'Completed' && $incident->status !== 'Approved' && $incident->status !== 'Closed'))
                <form action="{{ route('hse.dashboard.incidents.approve', $incident->id) }}" method="POST" onsubmit="return confirm('Setujui insiden ini secara resmi?')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
                        Approve Insiden
                    </button>
                </form>
            @endif

            @if($incident->status === 'Approved')
                <form action="{{ route('hse.dashboard.incidents.close', $incident->id) }}" method="POST" onsubmit="return confirm('Tutup insiden ini secara permanen?')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900">
                        Tutup Insiden (Close)
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('ok'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-800 font-medium">
            {{ session('ok') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs text-rose-800 font-medium">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informasi Utama Kejadian -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-[#0F172A] border-b border-slate-100 pb-2">Informasi Kejadian & Dampak</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block">Kategori Kejadian</span>
                        <strong class="text-slate-800">{{ $incident->category }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Tipe Insiden</span>
                        <strong class="text-slate-800">{{ $incident->type }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Lokasi Spesifik</span>
                        <strong class="text-slate-800">{{ $incident->location }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Recordable Case</span>
                        <strong class="text-slate-800">{{ $incident->recordable ? 'Ya (Masuk KPI)' : 'Tidak' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Hari Kerja Hilang (LTI)</span>
                        <strong class="text-slate-800">{{ number_format($incident->lost_time_days) }} hari</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Kerugian Aset (PD Cost)</span>
                        <strong class="text-slate-800">Rp {{ number_format($incident->property_damage_cost, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="pt-2">
                    <span class="text-xs text-slate-400 block mb-1">Deskripsi Rinci Insiden</span>
                    <div class="rounded-lg bg-slate-50 p-3 text-xs text-slate-700 leading-relaxed border border-slate-100 whitespace-pre-wrap">{{ $incident->description }}</div>
                </div>

                @if($incident->immediate_action)
                    <div class="pt-2">
                        <span class="text-xs text-slate-400 block mb-1">Tindakan Cepat / Immediate Action</span>
                        <div class="rounded-lg bg-slate-50 p-3 text-xs text-slate-700 leading-relaxed border border-slate-100 whitespace-pre-wrap">{{ $incident->immediate_action }}</div>
                    </div>
                @endif

                @if($incident->injured_name || $incident->injury_type)
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs font-semibold text-slate-700 block mb-1">Data Cedera Korban</span>
                        <p class="text-xs text-slate-600">
                            Nama Korban: <strong>{{ $incident->injured_name ?? '—' }}</strong> &bull; 
                            Jenis Cedera: <strong>{{ $incident->injury_type ?? '—' }}</strong>
                        </p>
                    </div>
                @endif

                @if($incident->environmental_impact)
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-xs font-semibold text-slate-700 block mb-1">Dampak Lingkungan</span>
                        <p class="text-xs text-slate-600">{{ $incident->environmental_impact }}</p>
                    </div>
                @endif

                @if($incident->evidence_path)
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-700">Lampiran Bukti Kejadian</span>
                        <a href="{{ asset('storage/' . $incident->evidence_path) }}" target="_blank" class="text-xs text-blue-600 hover:underline font-medium">
                            Buka / Unduh Lampiran &rarr;
                        </a>
                    </div>
                @endif
            </div>

            <!-- Hasil Investigasi & Root Cause -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-sm font-bold text-[#0F172A]">Hasil Investigasi & Akar Penyebab</h3>
                    <x-hse.status :value="$incident->investigation_status" />
                </div>

                @if($incident->investigation_status === 'Completed')
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-slate-400 block font-medium">Akar Masalah (Root Cause)</span>
                            <div class="rounded-lg bg-slate-50 p-3 text-slate-800 border border-slate-100 mt-1">{{ $incident->root_cause }}</div>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Tindakan Korektif (Corrective Action)</span>
                            <div class="rounded-lg bg-slate-50 p-3 text-slate-800 border border-slate-100 mt-1">{{ $incident->corrective_action }}</div>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Tindakan Preventif (Preventive Action)</span>
                            <div class="rounded-lg bg-slate-50 p-3 text-slate-800 border border-slate-100 mt-1">{{ $incident->preventive_action }}</div>
                        </div>
                    </div>
                @else
                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-xs text-amber-800">
                        Investigasi belum diselesaikan. Klik tombol <strong>Input Hasil Investigasi</strong> di atas untuk melengkapi root cause dan tindakan pencegahan.
                    </div>
                @endif
            </div>

            <!-- Tindakan Korektif Terkait (PICA) -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-[#0F172A] border-b border-slate-100 pb-2">Tindakan Korektif Terintegrasi (PICA)</h3>
                @if($picas->count() > 0)
                    <div class="ui-table-scroll">
                        <table class="ui-table text-xs">
                            <thead>
                                <tr>
                                    <th>No PICA</th>
                                    <th>Tindakan / Judul</th>
                                    <th>PIC</th>
                                    <th>Target Selesai</th>
                                    <th>Status PICA</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($picas as $pica)
                                    <tr>
                                        <td><strong>{{ $pica->action_no }}</strong></td>
                                        <td>{{ $pica->title }}</td>
                                        <td>{{ $pica->assignee?->name ?? '—' }}</td>
                                        <td>{{ $pica->due_date?->format('d M Y') ?? '—' }}</td>
                                        <td><x-hse.status :value="$pica->status" /></td>
                                        <td>
                                            <a href="{{ route('hse.dashboard.pica.detail', $pica->id) }}" class="text-xs text-blue-600 hover:underline font-semibold">
                                                Detail &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-xs text-slate-500">Tidak ada tindakan PICA terdaftar untuk insiden ini.</p>
                @endif
            </div>
        </div>

        <!-- Sidebar Workflow & Audit Trail -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-[#0F172A] border-b border-slate-100 pb-2">Status & Pelacak Workflow</h3>
                
                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-500">Pelapor (Reporter)</span>
                        <strong class="text-slate-800">{{ $incident->reporter?->name ?? '—' }}</strong>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-500">Tanggal Lapor</span>
                        <span class="text-slate-800">{{ $incident->reported_at?->format('d M Y H:i') ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-500">Reviewer</span>
                        <span class="text-slate-800">{{ $incident->reviewer?->name ?? 'Menunggu review' }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-500">Waktu Review</span>
                        <span class="text-slate-800">{{ $incident->reviewed_at?->format('d M Y H:i') ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-500">Investigator</span>
                        <span class="text-slate-800">{{ $incident->investigator?->name ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-500">Approver</span>
                        <span class="text-slate-800">{{ $incident->approver?->name ?? 'Menunggu approval' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Waktu Ditutup</span>
                        <span class="text-slate-800">{{ $incident->closed_at?->format('d M Y H:i') ?? 'Belum ditutup' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Review Insiden -->
<dialog id="modal-review" class="rounded-xl border border-slate-200 p-0 shadow-xl max-w-lg w-full backdrop:bg-slate-900/50">
    <div class="p-5">
        <h3 class="text-base font-bold text-[#0F172A] mb-3">Review & Verifikasi Laporan Insiden</h3>
        <form action="{{ route('hse.dashboard.incidents.review', $incident->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Hasil Review</label>
                <textarea name="review_notes" rows="3" class="w-full text-xs rounded-lg border border-slate-300 p-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Masukkan catatan review kelengkapan data kejadian..."></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-review').close()" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700">Simpan Review</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Modal Input Hasil Investigasi -->
<dialog id="modal-investigate" class="rounded-xl border border-slate-200 p-0 shadow-xl max-w-2xl w-full backdrop:bg-slate-900/50">
    <div class="p-5">
        <h3 class="text-base font-bold text-[#0F172A] mb-3">Input Hasil Investigasi Insiden</h3>
        <form action="{{ route('hse.dashboard.incidents.investigate', $incident->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Akar Masalah (Root Cause) <span class="text-rose-500">*</span></label>
                <textarea name="root_cause" rows="3" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Jelaskan akar penyebab (5 Whys / Ishikawa)...">{{ $incident->root_cause }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tindakan Korektif (Corrective Action) <span class="text-rose-500">*</span></label>
                <textarea name="corrective_action" rows="2" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Tindakan koreksi langsung...">{{ $incident->corrective_action }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tindakan Pencegahan (Preventive Action) <span class="text-rose-500">*</span></label>
                <textarea name="preventive_action" rows="2" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Tindakan agar tidak terulang...">{{ $incident->preventive_action }}</textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-investigate').close()" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">Simpan Investigasi</button>
            </div>
        </form>
    </div>
</dialog>
@endsection
