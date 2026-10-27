<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Hazard Report - SHE-FM-007</h2>
            <a href="{{ route('hse.dashboard.sap.hazard.index') }}" class="px-3 py-1.5 text-xs rounded bg-slate-200">Kembali</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border rounded-lg shadow-sm">
                <form method="POST" action="{{ route('hse.dashboard.sap.hazard.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="text-xs text-slate-600">Site</label>
                            <select name="site_id" class="w-full mt-1 border rounded px-2 py-1.5">
                                <option value="">- Pilih Site -</option>
                                @foreach(($options['sites'] ?? []) as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-600">Departemen</label>
                            <select name="department_id" class="w-full mt-1 border rounded px-2 py-1.5">
                                <option value="">- Pilih Departemen -</option>
                                @foreach(($options['departments'] ?? []) as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-600">Tanggal Dilaporkan</label>
                            <input type="date" name="report_date" value="{{ date('Y-m-d') }}" class="w-full mt-1 border rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="text-xs text-slate-600">Waktu Dilaporkan</label>
                            <input type="time" name="report_time" value="{{ date('H:i') }}" class="w-full mt-1 border rounded px-2 py-1.5">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600">Lokasi Bahaya *</label>
                            <input type="text" name="location" required class="w-full mt-1 border rounded px-2 py-1.5">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600">Detail Lokasi</label>
                            <input type="text" name="location_detail" class="w-full mt-1 border rounded px-2 py-1.5">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600">Aktivitas Terkait</label>
                            <input type="text" name="activity_related" class="w-full mt-1 border rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="text-xs text-slate-600">Kategori Bahaya *</label>
                            <select name="hazard_type" required class="w-full mt-1 border rounded px-2 py-1.5">
                                <option value="Unsafe Condition">Unsafe Condition</option>
                                <option value="Unsafe Action">Unsafe Action</option>
                                <option value="Environment">Environment</option>
                                <option value="Equipment">Equipment</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600">Deskripsi Bahaya *</label>
                            <textarea name="hazard_description" required class="w-full mt-1 border rounded px-2 py-1.5" rows="3"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600">Potensi Dampak</label>
                            <textarea name="potential_impact" class="w-full mt-1 border rounded px-2 py-1.5" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button class="px-4 py-2 rounded bg-[#0B2345] text-white text-sm">Buat Laporan & Lanjut Penilaian Risiko</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
