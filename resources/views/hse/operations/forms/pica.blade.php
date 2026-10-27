<form action="{{ route('hse.dashboard.pica.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Sumber Tindakan (Source Module) *</label>
                        <select name="source_module" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="PICA Audit" @selected(old('source_module') !== null && (string)old('source_module') === (string)('PICA Audit'))>PICA Audit</option>
                            <option value="PICA Investigation" @selected(old('source_module') !== null && (string)old('source_module') === (string)('PICA Investigation'))>PICA Investigation</option>
                            <option value="PICA Inspection" @selected(old('source_module') !== null && (string)old('source_module') === (string)('PICA Inspection'))>PICA Inspection</option>
                            <option value="PICA PTO" @selected(old('source_module') !== null && (string)old('source_module') === (string)('PICA PTO'))>PICA PTO</option>
                            <option value="PICA Hazard Report" @selected(old('source_module') !== null && (string)old('source_module') === (string)('PICA Hazard Report'))>PICA Hazard Report</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Prioritas *</label>
                        <select name="priority" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="Low" @selected(old('priority') !== null && (string)old('priority') === (string)('Low'))>Low</option>
                            <option value="Medium" selected @selected(old('priority') !== null && (string)old('priority') === (string)('Medium'))>Medium</option>
                            <option value="High" @selected(old('priority') !== null && (string)old('priority') === (string)('High'))>High</option>
                            <option value="Critical" @selected(old('priority') !== null && (string)old('priority') === (string)('Critical'))>Critical</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Site *</label>
                        <select name="site_id" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            @foreach($options['sites'] ?? [] as $site)
                                <option value="{{ $site->id }}" @selected(old('site_id') !== null && (string)old('site_id') === (string)($site->id))>{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Department</label>
                        <select name="department_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="" @selected(old('department_id') !== null && (string)old('department_id') === (string)(''))>-- Pilih Department --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(old('department_id') !== null && (string)old('department_id') === (string)($dept->id))>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul PICA / Finding Summary *</label>
                    <input type="text" name="title" required placeholder="Contoh: Perbaikan Pengaman Conveyor Line 2" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" value="{{ old('title') }}">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Temuan (Finding Description) *</label>
                    <textarea name="description" rows="2" required placeholder="Deskripsi ketidaksesuaian atau deviasi..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Root Cause (Akar Masalah)</label>
                        <textarea name="root_cause" rows="2" placeholder="Analisa penyebab utama..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">{{ old('root_cause') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Corrective Action (Tindakan Korektif)</label>
                        <textarea name="corrective_action" rows="2" placeholder="Tindakan langsung perbaikan..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">{{ old('corrective_action') }}</textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Preventive Action (Tindakan Pencegahan)</label>
                    <textarea name="preventive_action" rows="2" placeholder="Tindakan pencegahan agar tidak berulang..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">{{ old('preventive_action') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Penanggung Jawab (PIC)</label>
                        <select name="assigned_to" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="" @selected(old('assigned_to') !== null && (string)old('assigned_to') === (string)(''))>-- Pilih PIC --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" @selected(old('assigned_to') !== null && (string)old('assigned_to') === (string)($u->id))>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Waktu (Due Date) *</label>
                        <input type="date" name="due_date" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" value="{{ old('due_date') }}">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <x-ui.back-link href="{{ route('hse.dashboard.pica.list') }}">Batal</x-ui.back-link>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-[#1646A0] hover:bg-[#123880] rounded-lg shadow-sm">Terbitkan PICA</button>
                </div>
            </form>
