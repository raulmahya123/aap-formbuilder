@extends('layouts.app')

@section('content')
<div class="p-6 max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">➕ Tambah HIPO / Nearmiss</h1>

        <a href="{{ route('admin.hipo.index') }}"
           class="text-sm px-4 py-2 border rounded-lg hover:bg-gray-100">
            ← Kembali
        </a>
    </div>

    <form method="POST"
          action="{{ route('admin.hipo.store') }}"
          enctype="multipart/form-data"
          class="bg-white border rounded-xl p-6 space-y-4">

        @csrf

        <div class="grid md:grid-cols-2 gap-4">

            <div>
                <label class="text-xs">Tanggal</label>
                <input type="datetime-local"
                       name="report_time"
                       class="w-full border rounded-lg px-3 py-2"
                       required>
            </div>

            <div>
                <label class="text-xs">Jobsite</label>
                <input type="text"
                       name="jobsite"
                       class="w-full border rounded-lg px-3 py-2"
                       required>
            </div>

            <div>
                <label class="text-xs">Jenis</label>
                <select name="jenis_hipo"
                        class="w-full border rounded-lg px-3 py-2">
                    <option value="HIPO">HIPO</option>
                    <option value="Nearmiss">Nearmiss</option>
                </select>
            </div>

            <div>
                <label class="text-xs">Risk Level</label>
                <select name="risk_level"
                        class="w-full border rounded-lg px-3 py-2">
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Extreme">Extreme</option>
                </select>
            </div>

        </div>

        <div>
            <label class="text-xs">Kategori</label>
            <input type="text"
                   name="category"
                   class="w-full border rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="text-xs">KTA (Kondisi Tidak Aman)</label>
            <textarea name="kta"
                      rows="3"
                      class="w-full border rounded-lg px-3 py-2"></textarea>
        </div>

        <div>
            <label class="text-xs">TTA (Tindakan Tidak Aman)</label>
            <textarea name="tta"
                      rows="3"
                      class="w-full border rounded-lg px-3 py-2"></textarea>
        </div>

        <div>
            <label class="text-xs">Deskripsi Kejadian</label>
            <textarea name="description"
                      rows="3"
                      class="w-full border rounded-lg px-3 py-2"></textarea>
        </div>

        <div>
            <label class="text-xs">Konsekuensi Potensial</label>
            <textarea name="potential_consequence"
                      rows="2"
                      class="w-full border rounded-lg px-3 py-2"></textarea>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox"
                   name="stop_work"
                   value="1">
            <label class="text-sm">Stop Work</label>
        </div>

        <div class="flex justify-end">
            <button class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                Simpan
            </button>
        </div>

    </form>

</div>
@endsection
