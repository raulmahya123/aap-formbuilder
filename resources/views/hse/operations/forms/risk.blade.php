<form action="{{ route('hse.dashboard.risk.assessment.store') }}" method="POST" class="space-y-4">
                @csrf
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
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Aktivitas Pekerjaan *</label>
                    <input type="text" name="activity" required placeholder="Contoh: Pengoperasian Hauling Truck Malam Hari" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" value="{{ old('activity') }}">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Bahaya (Hazard) *</label>
                        <input type="text" name="hazard" required placeholder="Contoh: Tabrakan unit akibat blindspot & fatik" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" value="{{ old('hazard') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Bahaya *</label>
                        <select name="category" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="Operational" @selected(old('category') !== null && (string)old('category') === (string)('Operational'))>Operational</option>
                            <option value="Mechanical" @selected(old('category') !== null && (string)old('category') === (string)('Mechanical'))>Mechanical</option>
                            <option value="Electrical" @selected(old('category') !== null && (string)old('category') === (string)('Electrical'))>Electrical</option>
                            <option value="Geotechnical" @selected(old('category') !== null && (string)old('category') === (string)('Geotechnical'))>Geotechnical</option>
                            <option value="Chemical" @selected(old('category') !== null && (string)old('category') === (string)('Chemical'))>Chemical</option>
                            <option value="Environmental" @selected(old('category') !== null && (string)old('category') === (string)('Environmental'))>Environmental</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Existing Controls (Kontrol Saat Ini)</label>
                    <textarea name="existing_controls" rows="2" placeholder="Kontrol yang sudah ada..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">{{ old('existing_controls') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Inherent Likelihood (1-5) *</label>
                        <select name="likelihood" required class="w-full text-xs rounded-lg border-slate-300">
                            <option value="1" @selected(old('likelihood') !== null && (string)old('likelihood') === (string)('1'))>1 - Rare</option>
                            <option value="2" @selected(old('likelihood') !== null && (string)old('likelihood') === (string)('2'))>2 - Unlikely</option>
                            <option value="3" selected @selected(old('likelihood') !== null && (string)old('likelihood') === (string)('3'))>3 - Possible</option>
                            <option value="4" @selected(old('likelihood') !== null && (string)old('likelihood') === (string)('4'))>4 - Likely</option>
                            <option value="5" @selected(old('likelihood') !== null && (string)old('likelihood') === (string)('5'))>5 - Almost Certain</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Inherent Consequence (1-5) *</label>
                        <select name="consequence" required class="w-full text-xs rounded-lg border-slate-300">
                            <option value="1" @selected(old('consequence') !== null && (string)old('consequence') === (string)('1'))>1 - Insignificant</option>
                            <option value="2" @selected(old('consequence') !== null && (string)old('consequence') === (string)('2'))>2 - Minor</option>
                            <option value="3" selected @selected(old('consequence') !== null && (string)old('consequence') === (string)('3'))>3 - Moderate</option>
                            <option value="4" @selected(old('consequence') !== null && (string)old('consequence') === (string)('4'))>4 - Major</option>
                            <option value="5" @selected(old('consequence') !== null && (string)old('consequence') === (string)('5'))>5 - Catastrophic</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Residual Likelihood (Sesudah Kontrol)</label>
                        <select name="residual_likelihood" class="w-full text-xs rounded-lg border-slate-300">
                            <option value="1" @selected(old('residual_likelihood') !== null && (string)old('residual_likelihood') === (string)('1'))>1 - Rare</option>
                            <option value="2" selected @selected(old('residual_likelihood') !== null && (string)old('residual_likelihood') === (string)('2'))>2 - Unlikely</option>
                            <option value="3" @selected(old('residual_likelihood') !== null && (string)old('residual_likelihood') === (string)('3'))>3 - Possible</option>
                            <option value="4" @selected(old('residual_likelihood') !== null && (string)old('residual_likelihood') === (string)('4'))>4 - Likely</option>
                            <option value="5" @selected(old('residual_likelihood') !== null && (string)old('residual_likelihood') === (string)('5'))>5 - Almost Certain</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Residual Consequence</label>
                        <select name="residual_consequence" class="w-full text-xs rounded-lg border-slate-300">
                            <option value="1" @selected(old('residual_consequence') !== null && (string)old('residual_consequence') === (string)('1'))>1 - Insignificant</option>
                            <option value="2" selected @selected(old('residual_consequence') !== null && (string)old('residual_consequence') === (string)('2'))>2 - Minor</option>
                            <option value="3" @selected(old('residual_consequence') !== null && (string)old('residual_consequence') === (string)('3'))>3 - Moderate</option>
                            <option value="4" @selected(old('residual_consequence') !== null && (string)old('residual_consequence') === (string)('4'))>4 - Major</option>
                            <option value="5" @selected(old('residual_consequence') !== null && (string)old('residual_consequence') === (string)('5'))>5 - Catastrophic</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Rencana Mitigasi (Treatment)</label>
                        <input type="text" name="treatment" placeholder="Tindakan mitigasi tambahan..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" value="{{ old('treatment') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Due Date Mitigasi</label>
                        <input type="date" name="treatment_due_date" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" value="{{ old('treatment_due_date') }}">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Penanggung Jawab (PIC)</label>
                    <select name="owner_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                        <option value="" @selected(old('owner_id') !== null && (string)old('owner_id') === (string)(''))>-- Pilih PIC --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(old('owner_id') !== null && (string)old('owner_id') === (string)($u->id))>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <x-ui.back-link href="{{ route('hse.dashboard.risk.list') }}">Batal</x-ui.back-link>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-[#1646A0] hover:bg-[#123880] rounded-lg shadow-sm">Simpan Risk Assessment</button>
                </div>
            </form>
