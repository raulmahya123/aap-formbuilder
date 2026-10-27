@props(['action' => 'view', 'label' => null, 'ability' => null, 'subject' => null])
@php
    $icons = ['view'=>'eye', 'edit'=>'pencil', 'delete'=>'trash-2', 'members'=>'users', 'activate'=>'circle-check', 'approve'=>'circle-check', 'deactivate'=>'ban', 'download'=>'download', 'upload'=>'upload', 'reset-password'=>'key-round', 'more'=>'ellipsis', 'duplicate'=>'copy', 'assign'=>'user-round-plus', 'history'=>'history'];
    $labels = ['view'=>'Lihat detail', 'edit'=>'Edit', 'delete'=>'Hapus', 'members'=>'Kelola anggota', 'activate'=>'Aktifkan', 'approve'=>'Setujui', 'deactivate'=>'Nonaktifkan', 'download'=>'Unduh', 'upload'=>'Unggah', 'reset-password'=>'Reset password', 'more'=>'Aksi lainnya', 'duplicate'=>'Duplikat', 'assign'=>'Atur akses', 'history'=>'Riwayat'];
    $icons += ['print'=>'printer', 'pdf'=>'file-down', 'checklist'=>'clipboard-list', 'save'=>'save'];
    $labels += ['print'=>'Cetak', 'pdf'=>'Unduh PDF', 'checklist'=>'Isi checklist', 'save'=>'Simpan perubahan'];
    $label = $label ?? ($labels[$action] ?? 'Aksi lainnya');
    $attrs = $attributes->class(['ui-icon-button', 'ui-icon-button--'.$action])->merge(['title'=>$label, 'aria-label'=>$label]);
@endphp
@if($ability === null || \Illuminate\Support\Facades\Gate::allows($ability, $subject))
@if($attributes->has('href'))
    <a {{ $attrs }}><x-ui.lucide :name="$icons[$action] ?? 'ellipsis'" /></a>
@else
    <button {{ $attrs->merge(['type'=>'button']) }}><x-ui.lucide :name="$icons[$action] ?? 'ellipsis'" /></button>
@endif
@endif
