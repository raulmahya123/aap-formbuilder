<form action="{{ route('hse.dashboard.documents.store') }}" method="POST" enctype="multipart/form-data" >
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block">Kode Dokumen *</label>
                        <input type="text" name="document_code" required placeholder="Contoh: SOP-SHE-001" class="w-full ui-input" value="{{ old('document_code') }}">
                    </div>
                    <div>
                        <label class="block">Kategori Dokumen *</label>
                        <select name="category" required class="w-full ui-input">
                            <option value="Policy" {{ $category === 'policy' ? 'selected' : '' }}>Policy (Kebijakan)</option>
                            <option value="SOP" {{ $category === 'sop' ? 'selected' : '' }}>SOP (Standard Operating Procedure)</option>
                            <option value="IK" {{ $category === 'ik' ? 'selected' : '' }}>IK (Instruksi Kerja)</option>
                            <option value="Form" {{ $category === 'form' ? 'selected' : '' }}>Form Dokumen</option>
                            <option value="Other" @selected(old('category') !== null && (string)old('category') === (string)('Other'))>Lainnya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block">Judul Dokumen *</label>
                    <input type="text" name="title" required placeholder="Judul resmi dokumen..." class="w-full ui-input" value="{{ old('title') }}">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block">Department Owner *</label>
                        <select name="department_id" required class="w-full ui-input">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(old('department_id') !== null && (string)old('department_id') === (string)($dept->id))>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block">Versi Dokumen *</label>
                        <input type="text" name="version" required value="1.0" class="w-full ui-input">
                    </div>
                </div>

                <div>
                    <label class="block">Deskripsi / Ruang Lingkup</label>
                    <textarea name="description" rows="2" placeholder="Ruang lingkup penerapan dokumen..." class="w-full ui-input">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block">Tanggal Efektif *</label>
                        <input type="date" name="effective_date" required value="{{ date('Y-m-d') }}" class="w-full ui-input">
                    </div>
                    <div>
                        <label class="block">Tanggal Review Ulang</label>
                        <input type="date" name="review_date" value="{{ date('Y-m-d', strtotime('+1 year')) }}" class="w-full ui-input">
                    </div>
                </div>

                <div>
                    <label class="block">File Dokumen (PDF, DOCX) *</label>
                    <input type="file" name="file_document" required class="w-full">
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <x-ui.back-link href="{{ route('hse.dashboard.documents.index') }}">Batal</x-ui.back-link>
                    <x-ui.button variant="primary" icon="save" type="submit">Simpan Dokumen</x-ui.button>
                </div>
            </form>
