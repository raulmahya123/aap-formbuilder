<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah PTO - Informasi Umum</h2>
            <a href="{{ route('hse.sap.pto.index') }}" class="px-3 py-1.5 text-xs rounded bg-slate-200">Kembali</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border rounded-lg shadow-sm">
                <form method="POST" action="{{ route('hse.sap.pto.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-slate-600">Site / Lokasi Kerja</label>
                            <select name="site_id" class="w-full mt-1 border rounded px-2 py-1.5 text-sm">
                                <option value="">- Pilih Site -</option>
                                @foreach(($options['sites'] ?? []) as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-600">Departemen</label>
                            <select name="department_id" class="w-full mt-1 border rounded px-2 py-1.5 text-sm">
                                <option value="">- Pilih Departemen -</option>
                                @foreach(($options['departments'] ?? []) as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600">Pekerjaan yang Diamati *</label>
                            <textarea name="work_activity" required class="w-full mt-1 border rounded px-2 py-1.5 text-sm" rows="2"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600">Prosedur yang Berkaitan (IK/JSA/Prosedur)</label>
                            <textarea name="related_procedure" class="w-full mt-1 border rounded px-2 py-1.5 text-sm" rows="2"></textarea>
                        </div>
                        <div>
                            <label class="text-xs text-slate-600">Tanggal Pelaksanaan *</label>
                            <input type="date" name="observation_date" required value="{{ date('Y-m-d') }}" class="w-full mt-1 border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-slate-600">Waktu Pengamatan</label>
                            <input type="time" name="observation_time" class="w-full mt-1 border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-slate-600">Durasi Pengamatan</label>
                            <input type="text" name="duration" placeholder="Contoh: 30 menit" class="w-full mt-1 border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-slate-600">Lokasi Kerja</label>
                            <input type="text" name="work_location" class="w-full mt-1 border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-slate-600">Karyawan yang Diamati</label>
                            <select name="observed_employee_id" class="w-full mt-1 border rounded px-2 py-1.5 text-sm">
                                <option value="">- Pilih Karyawan -</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} {{ $u->nik ? '('.$u->nik.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-600">Atasan Langsung / Supervisor Review</label>
                            <select name="supervisor_id" class="w-full mt-1 border rounded px-2 py-1.5 text-sm">
                                <option value="">- Pilih Supervisor -</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} {{ $u->nik ? '('.$u->nik.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button class="px-4 py-2 rounded bg-[#0B2345] text-white text-sm">Buat PTO & Lanjut Isi Checklist</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
