@props(['kind' => 'empty', 'title' => null, 'message' => null, 'actionHref' => null, 'actionLabel' => 'Tambah data', 'canCreate' => false])
@php
    [$defaultTitle, $defaultMessage] = match($kind) {
        'search' => ['Pencarian tidak ditemukan', 'Coba kata kunci lain atau hapus pencarian.'],
        'filter' => ['Tidak ada data yang sesuai', 'Ubah atau reset filter untuk melihat data lainnya.'],
        'forbidden' => ['Akses tidak tersedia', 'Hubungi administrator untuk memeriksa izin akun Anda.'],
        default => ['Belum ada data', 'Data akan ditampilkan di sini setelah tersedia.'],
    };
@endphp
<div {{ $attributes->class(['ui-feedback-state']) }} role="status">
    <x-ui.lucide :name="$kind === 'forbidden' ? 'ban' : 'search'" />
    <h3>{{ $title ?? $defaultTitle }}</h3><p>{{ $message ?? $defaultMessage }}</p>
    @if($canCreate && $actionHref && $kind === 'empty')<x-ui.link variant="primary" :href="$actionHref" icon="plus">{{ $actionLabel }}</x-ui.link>@endif
    {{ $slot }}
</div>
