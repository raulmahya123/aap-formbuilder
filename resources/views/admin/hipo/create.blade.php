@extends('layouts.app')

@section('content')
<x-ui.page class="w-full min-w-0 ">

    <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Tambah HIPO / Nearmiss</x-ui.page-title>
<x-ui.page-description>Lengkapi informasi untuk menambahkan laporan HIPO dan Nearmiss.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.back-link href="{{ route('admin.hipo.index') }}">Kembali</x-ui.back-link>
<x-ui.button form="page-primary-form" variant="primary" >
                <x-ui.lucide name="save" /> Simpan Laporan
            </x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>

    {{-- ERROR ALERT --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg">
            <ul class="text-sm list-disc ml-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="page-primary-form" method="POST"
        action="{{ route('admin.hipo.store') }}"
        enctype="multipart/form-data"
        class="ui-card">
  @csrf

        {{-- ================= DATA UMUM ================= --}}
        <div>
            <x-ui.card-title ><x-ui.lucide name="clipboard-list" /> Data Umum</x-ui.card-title>

            <div class="grid md:grid-cols-3 gap-4">

                {{-- REPORT TIME --}}
                <div>
                    <label >Tanggal & Waktu</label>
                    <input type="datetime-local"
                        name="report_time"
                        value="{{ old('report_time') }}"
                        required
                        class="w-full ui-input">
                </div>

                {{-- REPORTER --}}
                <div>
                    <label >Reporter</label>
                    <input type="text"
                        value="{{ auth()->user()->name }}"
                        class="w-full ui-input"
                        readonly>
                </div>

                {{-- JOBSITE --}}
                <div>
                    <label >Jobsite</label>
                    <select
                        id="hipo-site-id"
                        name="site_id"
                        required
                        class="w-full ui-input">
                        <option value="">-- Pilih Jobsite --</option>
                        @foreach(($sites ?? []) as $site)
                            <option value="{{ $site['id'] }}" {{ old('site_id') == $site['id'] ? 'selected' : '' }}>
                                {{ $site['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="hipo-assigned-admin-id">Admin Site penanggung jawab</label>
                    <select id="hipo-assigned-admin-id" name="assigned_admin_id" class="w-full ui-input">
                        <option value="">Belum ditugaskan</option>
                        <option id="hipo-admin-empty-option" value="" disabled hidden>Pilih site untuk melihat Admin Site</option>
                        @foreach($siteAdmins as $admin)
                            <option value="{{ $admin->id }}"
                                data-site-ids="{{ $admin->sites->pluck('id')->implode(',') }}"
                                @selected((string) old('assigned_admin_id') === (string) $admin->id)>
                                {{ $admin->name }} — {{ $admin->roleLabel() }}
                            </option>
                        @endforeach
                    </select>
                    <p id="hipo-admin-hint" class="mt-1 text-xs text-gray-500">Pilih site untuk melihat Admin Site yang memiliki akses. Admin yang ditugaskan dapat menutup temuan tersebut.</p>
                    @if(auth()->user()->canUseFeature('site_access'))
                        <a id="hipo-admin-access-link" href="{{ route('admin.site_access.index') }}" class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-blue-700 hover:underline">
                            <x-ui.lucide name="user-round-plus" class="h-3.5 w-3.5" /> Atur role dan akses site
                        </a>
                    @endif
                    @error('assigned_admin_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- SHIFT --}}
                <div>
                    <label >Shift</label>
                    <select name="shift"
                        required
                        class="w-full ui-input">
                        <option value="">-- Pilih Shift --</option>
                        <option value="Shift 1" {{ old('shift') == 'Shift 1' ? 'selected' : '' }}>Shift 1</option>
                        <option value="Shift 2" {{ old('shift') == 'Shift 2' ? 'selected' : '' }}>Shift 2</option>
                    </select>
                </div>

                {{-- SUMBER --}}
                <div>
                    <label >Sumber</label>
                    <select name="source"
                        required
                        class="w-full ui-input">
                        <option value="">-- Pilih Sumber --</option>
                        <option value="Hazard Report" {{ old('source') == 'Hazard Report' ? 'selected' : '' }}>Hazard Report</option>
                        <option value="Safety Inspection" {{ old('source') == 'Safety Inspection' ? 'selected' : '' }}>Safety Inspection</option>
                        <option value="PTO" {{ old('source') == 'PTO' ? 'selected' : '' }}>PTO</option>
                    </select>
                </div>

                {{-- CATEGORY --}}
                <div>
                    <label >Kategori</label>
                    <select name="category"
                        required
                        class="w-full ui-input">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="High Potential Hazard" {{ old('category') == 'High Potential Hazard' ? 'selected' : '' }}>
                            High Potential Hazard
                        </option>
                        <option value="Nearmiss" {{ old('category') == 'Nearmiss' ? 'selected' : '' }}>
                            Nearmiss
                        </option>
                    </select>
                </div>

                {{-- RISK LEVEL --}}
                <div>
                    <label >Risk Level</label>
                    <select name="risk_level"
                        required
                        class="w-full ui-input">
                        @foreach(['Low','Medium','High','Extreme'] as $risk)
                            <option value="{{ $risk }}"
                                {{ old('risk_level') == $risk ? 'selected' : '' }}>
                                {{ $risk }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- STOP WORK --}}
                <div class="flex items-center gap-2 mt-6">
                    <input type="checkbox"
                        name="stop_work"
                        value="1"
                        {{ old('stop_work') ? 'checked' : '' }}>
                    <label >Stop Work</label>
                </div>

            </div>
        </div>

        {{-- ================= RINCIAN ================= --}}
        <div>
            <x-ui.card-title ><x-ui.lucide name="pencil" /> Rincian Kejadian</x-ui.card-title>

            <div class="space-y-4">

                <div>
                    <label >KTA</label>
                    <textarea name="kta"
                        rows="3"
                        required
                        class="w-full ui-input">{{ old('kta') }}</textarea>
                </div>

                <div>
                    <label >TTA</label>
                    <textarea name="tta"
                        rows="3"
                        required
                        class="w-full ui-input">{{ old('tta') }}</textarea>
                </div>

                <div>
                    <label >Deskripsi Kejadian</label>
                    <textarea name="description"
                        rows="3"
                        required
                        class="w-full ui-input">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label >Konsekuensi Potensial</label>
                    <select name="potential_consequence"
                        required
                        class="w-full ui-input">
                        <option value="">-- Pilih Konsekuensi --</option>
                        @foreach([
                            'Fatality',
                            'LTI',
                            'Injury Non LTI',
                            'Property Damage',
                            'Environment Accident'
                        ] as $consequence)
                            <option value="{{ $consequence }}"
                                {{ old('potential_consequence') == $consequence ? 'selected' : '' }}>
                                {{ $consequence }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- ================= KONTROL RISIKO ================= --}}
        <div>
            <x-ui.card-title ><x-ui.lucide name="shield" /> Kontrol Risiko</x-ui.card-title>

            <div class="grid md:grid-cols-2 gap-5">

                @foreach([
                    'engineering' => 'Rekayasa Engineering',
                    'administrative' => 'Administratif',
                    'work_practice' => 'Praktek Kerja',
                    'ppe' => 'APD'
                ] as $key => $label)

                <div class="border rounded-lg p-4 space-y-3 bg-gray-50">

                    <h4 class="font-semibold text-sm">{{ $label }}</h4>

                    <textarea name="control_{{ $key }}"
                        rows="2"
                        class="w-full ui-input"
                        placeholder="Tindakan kontrol...">{{ old('control_'.$key) }}</textarea>

                    <x-ui.user-name-select name="pic_{{ $key }}" :users="$picUsers" :value="old('pic_'.$key)" aria-label="PIC {{ $label }}" />

                    <input type="file"
                        name="evidence_{{ $key }}"
                        class="w-full">

                </div>

                @endforeach

            </div>
        </div>

        {{-- SUBMIT --}}
        <div class="flex justify-end">

        </div>

    </form>

    <script>
        (() => {
            const siteSelect = document.getElementById('hipo-site-id');
            const adminSelect = document.getElementById('hipo-assigned-admin-id');
            const hint = document.getElementById('hipo-admin-hint');
            const emptyOption = document.getElementById('hipo-admin-empty-option');
            const accessLink = document.getElementById('hipo-admin-access-link');
            if (!siteSelect || !adminSelect || !hint) return;

            const updateAdminOptions = () => {
                const siteId = siteSelect.value;
                let eligibleCount = 0;
                [...adminSelect.options].forEach((option) => {
                    if (!option.value) return;
                    const siteIds = (option.dataset.siteIds || '').split(',').filter(Boolean);
                    const eligible = Boolean(siteId) && siteIds.includes(siteId);
                    option.hidden = !eligible;
                    option.disabled = !eligible;
                    if (eligible) eligibleCount += 1;
                    if (!eligible && option.selected) adminSelect.value = '';
                });

                hint.textContent = !siteId
                    ? 'Pilih site untuk melihat Admin Site yang memiliki akses. Admin yang ditugaskan dapat menutup temuan tersebut.'
                    : eligibleCount
                        ? 'Admin Site ini memiliki akses ke site terpilih dan dapat menutup temuan tersebut.'
                        : 'Belum ada Admin Site aktif yang ditugaskan ke site ini. Minta Super Admin mengatur akses site terlebih dahulu.';

                if (emptyOption) {
                    emptyOption.textContent = !siteId ? 'Pilih site untuk melihat Admin Site' : 'Belum ada Admin Site aktif untuk site ini';
                    emptyOption.hidden = Boolean(siteId && eligibleCount);
                }
                if (accessLink && siteId) {
                    const url = new URL(accessLink.href);
                    url.searchParams.set('site_id', siteId);
                    accessLink.href = url.toString();
                }
            };

            siteSelect.addEventListener('change', updateAdminOptions);
            updateAdminOptions();
        })();
    </script>

</x-ui.page>
@endsection
