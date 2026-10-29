<form action="{{ route('hse.dashboard.training.record.store') }}" method="POST" >
            @csrf
            <div>
                <label class="block">Materi / Modul Pelatihan (Course) <span class="text-rose-500">*</span></label>
                <select name="course_id" required class="w-full ui-input">
                    <option value="" @selected(old('course_id') !== null && (string)old('course_id') === (string)(''))>-- Pilih Modul Pelatihan --</option>
                    @foreach($options['training_courses'] ?? [] as $crs)
                        <option value="{{ $crs->id }}" @selected(old('course_id') !== null && (string)old('course_id') === (string)($crs->id))>{{ $crs->name ?? $crs->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block">Nama Karyawan / Peserta <span class="text-rose-500">*</span></label>
                    <select name="user_id" required class="w-full ui-input">
                        <option value="" @selected(old('user_id') !== null && (string)old('user_id') === (string)(''))>-- Pilih Karyawan --</option>
                        @foreach($options['users'] ?? [] as $usr)
                            <option value="{{ $usr->id }}" @selected(old('user_id') !== null && (string)old('user_id') === (string)($usr->id))>{{ $usr->name }} ({{ $usr->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block">Site Penugasan <span class="text-rose-500">*</span></label>
                    <select name="site_id" required class="w-full ui-input">
                        <option value="" @selected(old('site_id') !== null && (string)old('site_id') === (string)(''))>-- Pilih Site --</option>
                        @foreach($options['sites'] ?? [] as $st)
                            <option value="{{ $st->id }}" {{ ($filters['site_id'] ?? null) == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block">Tanggal Jadwal <span class="text-rose-500">*</span></label>
                    <input type="date" name="scheduled_date" required value="{{ now()->toDateString() }}" class="w-full ui-input">
                </div>
                <div>
                    <label class="block">Tanggal Selesai Pelatihan</label>
                    <input type="date" name="completed_date" value="{{ now()->toDateString() }}" class="w-full ui-input">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block">Nilai Evaluasi / Ujian (0 - 100)</label>
                    <input type="number" name="score" min="0" max="100" step="0.1" value="85" class="w-full ui-input">
                </div>
                <div>
                    <label class="block">Status Keikutsertaan <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full ui-input">
                        <option value="Completed" selected @selected(old('status') !== null && (string)old('status') === (string)('Completed'))>Completed (Lulus/Selesai)</option>
                        <option value="In Progress" @selected(old('status') !== null && (string)old('status') === (string)('In Progress'))>In Progress</option>
                        <option value="Scheduled" @selected(old('status') !== null && (string)old('status') === (string)('Scheduled'))>Scheduled</option>
                        <option value="Not Started" @selected(old('status') !== null && (string)old('status') === (string)('Not Started'))>Not Started</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-200">
                <x-ui.back-link href="{{ route('hse.dashboard.training') }}">Batal</x-ui.back-link>
                <x-ui.button variant="primary" icon="save" type="submit">Simpan Catatan Pelatihan</x-ui.button>
            </div>
        </form>
