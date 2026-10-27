<form action="{{ route('hse.dashboard.incidents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Site Operasional <span class="text-rose-500">*</span></label>
                    <select name="site_id" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="" @selected(old('site_id') !== null && (string)old('site_id') === (string)(''))>-- Pilih Site --</option>
                        @foreach($options['sites'] ?? [] as $st)
                            <option value="{{ $st->id }}" {{ ($filters['site_id'] ?? null) == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen Terkait</label>
                    <select name="department_id" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="" @selected(old('department_id') !== null && (string)old('department_id') === (string)(''))>-- Pilih Departemen --</option>
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
                        <option value="Incident" @selected(old('category') !== null && (string)old('category') === (string)('Incident'))>Incident</option>
                        <option value="Near Miss" @selected(old('category') !== null && (string)old('category') === (string)('Near Miss'))>Near Miss</option>
                        <option value="Occupational Illness" @selected(old('category') !== null && (string)old('category') === (string)('Occupational Illness'))>Occupational Illness (PAK)</option>
                        <option value="Environmental" @selected(old('category') !== null && (string)old('category') === (string)('Environmental'))>Environmental</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Insiden (SHE Classification) <span class="text-rose-500">*</span></label>
                    <select name="type" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="Near Miss" @selected(old('type') !== null && (string)old('type') === (string)('Near Miss'))>Near Miss</option>
                        <option value="First Aid Case" @selected(old('type') !== null && (string)old('type') === (string)('First Aid Case'))>First Aid Case (FAC)</option>
                        <option value="Medical Treatment Case" @selected(old('type') !== null && (string)old('type') === (string)('Medical Treatment Case'))>Medical Treatment Case (MTC)</option>
                        <option value="Restricted Work Case" @selected(old('type') !== null && (string)old('type') === (string)('Restricted Work Case'))>Restricted Work Case (RWC)</option>
                        <option value="Lost Time Injury" @selected(old('type') !== null && (string)old('type') === (string)('Lost Time Injury'))>Lost Time Injury (LTI)</option>
                        <option value="Fatality" @selected(old('type') !== null && (string)old('type') === (string)('Fatality'))>Fatality</option>
                        <option value="Property Damage" @selected(old('type') !== null && (string)old('type') === (string)('Property Damage'))>Property Damage</option>
                        <option value="Environmental" @selected(old('type') !== null && (string)old('type') === (string)('Environmental'))>Environmental Incident</option>
                        <option value="Other" @selected(old('type') !== null && (string)old('type') === (string)('Other'))>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat Keparahan (Severity) <span class="text-rose-500">*</span></label>
                    <select name="severity" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="Low" @selected(old('severity') !== null && (string)old('severity') === (string)('Low'))>Low</option>
                        <option value="Medium" selected @selected(old('severity') !== null && (string)old('severity') === (string)('Medium'))>Medium</option>
                        <option value="High" @selected(old('severity') !== null && (string)old('severity') === (string)('High'))>High</option>
                        <option value="Critical" @selected(old('severity') !== null && (string)old('severity') === (string)('Critical'))>Critical</option>
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
                <x-ui.back-link href="{{ route('hse.dashboard.incidents') }}">Batal</x-ui.back-link>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold hover:bg-[#0B2345]">Submit Laporan Insiden</button>
            </div>
        </form>
