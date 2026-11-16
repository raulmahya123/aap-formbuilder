@extends('layouts.app')

@section('title', 'AHEAD — Andalan Health Evaluation And Detection')

@section('content')
<x-ui.page>
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <x-ui.page-title>AHEAD</x-ui.page-title>
            <x-ui.page-description>Andalan Health Evaluation And Detection · Rekap pemeriksaan kesiapan kerja sebelum shift.</x-ui.page-description>
        </div>

        <x-ui.page-actions>
            <x-ui.link
                variant="secondary"
                icon="file-down"
                :href="route('admin.wellness.export', request()->query())"
            >Unduh Excel</x-ui.link>
            <x-ui.link
                variant="secondary"
                :href="route('wellness.create')"
                target="_blank"
                rel="noopener"
            >Buka Form Publik</x-ui.link>
        </x-ui.page-actions>

        <x-slot:filters>
            <x-ui.table-toolbar>
                <x-ui.table-search
                    name="q"
                    label="Karyawan"
                    placeholder="Cari nama karyawan..."
                    aria-label="Cari karyawan"
                />
                <x-ui.table-search
                    name="site"
                    label="Site"
                    placeholder="Cari site..."
                    aria-label="Cari site"
                />
                <x-ui.table-filter
                    name="work_readiness"
                    :options="['ya' => 'Siap bekerja', 'tidak' => 'Tidak siap', 'off_day' => 'Off day']"
                    label="Kesiapan kerja"
                    placeholder="Semua kesiapan"
                />

                <x-slot:actions>
                    <x-ui.button variant="primary" type="submit">Terapkan</x-ui.button>
                    <x-ui.link variant="secondary" :href="route('admin.wellness.index')">Reset</x-ui.link>
                </x-slot:actions>
            </x-ui.table-toolbar>
        </x-slot:filters>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            'total' => ['Total Respons', 'circle-check'],
            'today' => ['Masuk Hari Ini', 'calendar-days'],
            'not_ready' => ['Tidak Siap / Off Day', 'alert-triangle'],
            'complaints' => ['Ada Keluhan', 'heart'],
        ] as $key => [$label, $icon])
            <x-ui.metric-card :label="$label" :value="$stats[$key]" :icon="$icon" />
        @endforeach
    </div>

    <x-ui.table :paginator="$responses">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Karyawan</th>
                <th>Jabatan</th>
                <th>Departemen</th>
                <th>Site</th>
                <th>Shift</th>
                <th>Kesiapan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($responses as $response)
                <tr>
                    <td class="whitespace-nowrap">{{ $response->created_at->format('d M Y H:i') }}</td>
                    <td>
                        <div class="font-medium">{{ $response->name }}</div>
                    </td>
                    <td>{{ $response->position ?: '—' }}</td>
                    <td>{{ $response->department ?: '—' }}</td>
                    <td>{{ $response->site ?: '—' }}</td>
                    <td>{{ $response->shift ? ucfirst($response->shift) : '—' }}</td>
                    <td>
                        <x-ui.status-badge :variant="match($response->work_readiness) {
                            'ya' => 'success',
                            'tidak' => 'danger',
                            'off_day' => 'warning',
                            default => 'neutral',
                        }">
                            {{ match($response->work_readiness) {
                                'ya' => 'Siap bekerja',
                                'tidak' => 'Tidak siap',
                                'off_day' => 'Off day',
                                default => 'Belum tersedia',
                            } }}
                        </x-ui.status-badge>
                    </td>
                    <td class="ui-action-cell">
                        <x-ui.table-actions>
                            <x-ui.icon-button action="view" label="Detail submission {{ $response->name }}" :data-dialog-open="'wellness-'.$response->id" />
                        </x-ui.table-actions>
                    </td>
                </tr>

                @push('modals')
                    <x-ui.modal-detail
                        :id="'wellness-'.$response->id"
                        :title="'Submission — '.$response->name"
                        description="Hasil pemeriksaan sebelum shift."
                    >
                        <x-ui.detail-section title="Data karyawan">
                            @foreach([
                                'Nama' => $response->name,
                                'Jabatan' => $response->position,
                                'Departemen' => $response->department,
                                'Site' => $response->site,
                                'Waktu' => $response->created_at->format('d M Y H:i'),
                            ] as $label => $value)
                                <x-ui.detail-row :label="$label">{{ filled($value) ? $value : '—' }}</x-ui.detail-row>
                            @endforeach
                        </x-ui.detail-section>

                        <x-ui.detail-section title="Kondisi sebelum shift">
                            @foreach([
                                'Shift' => $response->shift ? ucfirst($response->shift) : null,
                                'Durasi tidur' => str_replace('_', ' ', (string) $response->sleep_before_shift),
                                'Kualitas tidur' => $response->sleep_quality,
                                'Alasan tidur buruk' => $response->sleep_quality_reason,
                                'Mengantuk berat / sulit berkonsentrasi' => $response->heavy_drowsiness,
                                'Keluhan kesehatan' => match($response->health_complaint) {
                                    'ya' => 'Ada keluhan',
                                    'tidak' => 'Tidak ada keluhan',
                                    'ringan' => 'Keluhan ringan (data sebelumnya)',
                                    'berat' => 'Keluhan berat (data sebelumnya)',
                                    'tidak_ada' => 'Tidak ada keluhan (data sebelumnya)',
                                    default => null,
                                },
                                'Rincian keluhan / gejala' => $response->health_complaint_details,
                                'Obat berefek kantuk' => $response->drowsy_medication ? ucfirst($response->drowsy_medication) : null,
                                'Nama obat' => $response->drowsy_medication_details,
                                'Kesiapan kerja' => match($response->work_readiness) {
                                    'ya' => 'Siap bekerja',
                                    'tidak' => 'Tidak siap',
                                    'off_day' => 'Off day',
                                    default => null,
                                },
                            ] as $label => $value)
                                <x-ui.detail-row :label="$label">{{ filled($value) ? ucfirst((string) $value) : '—' }}</x-ui.detail-row>
                            @endforeach
                        </x-ui.detail-section>

                        @if($response->mood !== null || $response->stress_level !== null)
                            <x-ui.detail-section title="Pemeriksaan sebelumnya">
                                @foreach([
                                    'Mood' => $response->mood,
                                    'Tingkat stres' => $response->stress_level,
                                    'Tidur (jam)' => $response->sleep_hours,
                                    'Aktivitas fisik' => $response->physical_activity,
                                    'Catatan' => $response->notes,
                                ] as $label => $value)
                                    @if(filled($value))
                                        <x-ui.detail-row :label="$label">{{ $value }}</x-ui.detail-row>
                                    @endif
                                @endforeach
                            </x-ui.detail-section>
                        @endif
                    </x-ui.modal-detail>
                @endpush
            @empty
                <x-ui.table-empty-state colspan="8" />
            @endforelse
        </tbody>
    </x-ui.table>
</x-ui.page>
@endsection
