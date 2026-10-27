<form action="{{ route('hse.dashboard.environment.record.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Site Operasional <span class="text-rose-500">*</span></label>
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
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pencatatan <span class="text-rose-500">*</span></label>
                    <input type="date" name="recorded_date" required value="{{ now()->toDateString() }}" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Parameter / Metrik Lingkungan <span class="text-rose-500">*</span></label>
                    <select name="metric" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="Waste Generated" @selected(old('metric') !== null && (string)old('metric') === (string)('Waste Generated'))>Waste Generated (Total Limbah)</option>
                        <option value="Hazardous Waste" @selected(old('metric') !== null && (string)old('metric') === (string)('Hazardous Waste'))>Hazardous Waste (Limbah B3)</option>
                        <option value="Non-Hazardous Waste" @selected(old('metric') !== null && (string)old('metric') === (string)('Non-Hazardous Waste'))>Non-Hazardous Waste (Limbah Non-B3)</option>
                        <option value="Recycled Waste" @selected(old('metric') !== null && (string)old('metric') === (string)('Recycled Waste'))>Recycled Waste (Limbah Didaur Ulang)</option>
                        <option value="Energy Consumption" @selected(old('metric') !== null && (string)old('metric') === (string)('Energy Consumption'))>Energy Consumption (Listrik)</option>
                        <option value="Water Consumption" @selected(old('metric') !== null && (string)old('metric') === (string)('Water Consumption'))>Water Consumption (Air)</option>
                        <option value="Fuel Consumption" @selected(old('metric') !== null && (string)old('metric') === (string)('Fuel Consumption'))>Fuel Consumption (BBM Solar)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai / Jumlah Volume <span class="text-rose-500">*</span></label>
                    <input type="number" name="value" step="0.01" min="0" required placeholder="Contoh: 12.5" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan Pengukuran <span class="text-rose-500">*</span></label>
                    <select name="unit" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="Ton" @selected(old('unit') !== null && (string)old('unit') === (string)('Ton'))>Ton</option>
                        <option value="Kg" @selected(old('unit') !== null && (string)old('unit') === (string)('Kg'))>Kg</option>
                        <option value="kWh" @selected(old('unit') !== null && (string)old('unit') === (string)('kWh'))>kWh</option>
                        <option value="m³" @selected(old('unit') !== null && (string)old('unit') === (string)('m³'))>m³ (Meter Kubik)</option>
                        <option value="Liter" @selected(old('unit') !== null && (string)old('unit') === (string)('Liter'))>Liter</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan / Keterangan TPS</label>
                <textarea name="notes" rows="2" placeholder="Sumber limbah, nomor manifest B3, atau detail pencatatan..." class="w-full text-xs rounded-lg border border-slate-300 p-2.5"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-200">
                <x-ui.back-link href="{{ route('hse.dashboard.environment') }}">Batal</x-ui.back-link>
                <x-ui.button variant="primary" icon="save" type="submit">Simpan Rekam Lingkungan</x-ui.button>
            </div>
        </form>
