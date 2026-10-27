<form action="{{ route('hse.dashboard.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Dokumen *</label>
                        <input type="text" name="document_code" required placeholder="Contoh: SOP-SHE-001" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" value="{{ old('document_code') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Dokumen *</label>
                        <select name="category" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="Policy" {{ $category === 'policy' ? 'selected' : '' }}>Policy (Kebijakan)</option>
                            <option value="SOP" {{ $category === 'sop' ? 'selected' : '' }}>SOP (Standard Operating Procedure)</option>
                            <option value="IK" {{ $category === 'ik' ? 'selected' : '' }}>IK (Instruksi Kerja)</option>
                            <option value="Form" {{ $category === 'form' ? 'selected' : '' }}>Form Dokumen</option>
                            <option value="Other" @selected(old('category') !== null && (string)old('category') === (string)('Other'))>Lainnya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Dokumen *</label>
                    <input type="text" name="title" required placeholder="Judul resmi dokumen..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" value="{{ old('title') }}">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Department Owner *</label>
                        <select name="department_id" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(old('department_id') !== null && (string)old('department_id') === (string)($dept->id))>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Versi Dokumen *</label>
                        <input type="text" name="version" required value="1.0" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi / Ruang Lingkup</label>
                    <textarea name="description" rows="2" placeholder="Ruang lingkup penerapan dokumen..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Efektif *</label>
                        <input type="date" name="effective_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Review Ulang</label>
                        <input type="date" name="review_date" value="{{ date('Y-m-d', strtotime('+1 year')) }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">File Dokumen (PDF, DOCX) *</label>
                    <input type="file" name="file_document" required class="w-full text-xs rounded-lg border border-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#1646A0] hover:file:bg-blue-100">
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <x-ui.back-link href="{{ route('hse.dashboard.documents.index') }}">Batal</x-ui.back-link>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-[#1646A0] hover:bg-[#123880] rounded-lg shadow-sm">Simpan Dokumen</button>
                </div>
            </form>
