@extends('layouts.app')
@section('title', 'Management Form')
@section('content')
<x-ui.page>
<x-ui.page-header><div class="ui-page-header__copy"><x-ui.page-title>Management Form</x-ui.page-title><x-ui.page-description>Kelola SOP, instruksi kerja, dan form berdasarkan perusahaan, department, serta site.</x-ui.page-description></div><x-ui.page-actions>@can('create', [App\Models\Form::class, request('department_id') ? (int) request('department_id') : null])<x-ui.link data-modal-form icon="plus" variant="primary" :href="route('admin.forms.create', request()->only(['company_id','department_id','site_id','doc_type']))">Tambah Dokumen</x-ui.link>@endcan</x-ui.page-actions>
<x-slot:filters>
<x-ui.table-toolbar>
<x-ui.table-search name="q" placeholder="Cari judul dokumen…" aria-label="Cari dokumen" />
<label class="sr-only" for="mandala-company">Company</label><x-ui.form-select id="mandala-company" name="company_id" @change="$el.form.elements.site_id.value=''; $el.form.elements.department_id.value=''; $el.form.requestSubmit()"><option value="">Semua company</option>@foreach($companies as $company)<option value="{{ $company->id }}" @selected(request('company_id') == $company->id)>{{ $company->code }} — {{ $company->name }}</option>@endforeach</x-ui.form-select>
<x-ui.table-filter name="department_id" :options="$departments->pluck('name','id')" label="Department" placeholder="Semua department" />
<x-ui.table-filter name="site_id" :options="$sites->when(request('company_id'), fn ($items) => $items->where('company_id', request('company_id')))->pluck('name','id')" label="Site" placeholder="Semua site" />
<input type="hidden" name="doc_type" value="{{ request('doc_type') }}">
<x-slot:actions>
<x-ui.link variant="secondary" :href="route('admin.forms.index')">Reset</x-ui.link>

</x-slot:actions>
</x-ui.table-toolbar>
</x-slot:filters>
</x-ui.page-header>
<nav class="ui-hierarchy" aria-label="Hirarki dokumen"><span>Company</span><span aria-hidden="true">→</span><span>Department</span><span aria-hidden="true">→</span><span>Site</span><span aria-hidden="true">→</span><span>Tipe dokumen</span></nav>

<nav class="ui-segmented" aria-label="Tipe dokumen">@foreach([''=>'Semua','SOP'=>'SOP','IK'=>'IK','FORM'=>'FORM'] as $type=>$label)<a href="{{ route('admin.forms.index', array_merge(request()->except(['page','doc_type']), ['doc_type'=>$type])) }}" @if(request('doc_type','') === $type) aria-current="page" @endif>{{ $label }}</a>@endforeach</nav>
<x-ui.table :paginator="$forms"><thead><tr><th>Dokumen</th><th>Tipe</th><th>Company / Department</th><th>Site</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@forelse($forms as $f)<tr><td><div class="font-medium"><x-ui.truncated-text :value="$f->title" /></div><div class="text-xs text-zinc-600">{{ $f->type === 'pdf' ? 'Lampiran file' : 'Form builder' }}</div></td><td><x-ui.status-badge>{{ $f->doc_type ?? 'FORM' }}</x-ui.status-badge></td><td>{{ $f->company?->code ?? '—' }} / {{ $f->department?->name ?? '—' }}</td><td>{{ $f->site?->name ?? '—' }}</td><td><x-ui.status-badge :variant="$f->is_active ? 'success' : 'neutral'">{{ $f->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.status-badge></td><td class="ui-action-cell"><x-ui.table-actions><x-ui.icon-button action="view" ability="view" :subject="$f" :href="route('front.forms.show', $f->slug)" /><x-ui.icon-button data-modal-form action="edit" ability="update" :subject="$f" :href="route('admin.forms.edit', $f)" />@can('delete', $f)<form method="POST" action="{{ route('admin.forms.destroy',$f) }}" data-confirm='Hapus dokumen "{{ $f->title }}"?'>@csrf @method('DELETE')<x-ui.icon-button action="delete" type="submit" /></form>@endcan</x-ui.table-actions></td></tr>@empty<x-ui.table-empty-state colspan="6" />@endforelse</tbody></x-ui.table>
</x-ui.page>
@endsection