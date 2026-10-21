@props(['kind' => 'api', 'retryUrl' => null])
@php
    [$title, $message] = match((string) $kind) {
        'network' => ['Koneksi terputus', 'Periksa koneksi internet, lalu coba lagi.'],
        '401' => ['Sesi telah berakhir', 'Masuk kembali untuk melanjutkan aktivitas Anda.'],
        '403' => ['Akses ditolak', 'Akun Anda tidak memiliki izin. Hubungi administrator jika membutuhkan akses.'],
        '404' => ['Halaman tidak ditemukan', 'Halaman atau data yang dicari sudah tidak tersedia.'],
        '422' => ['Isian belum valid', 'Periksa kembali isian atau filter yang digunakan, lalu coba lagi.'],
        '500', '503' => ['Layanan sedang bermasalah', 'Data belum dapat dimuat. Silakan coba beberapa saat lagi.'],
        default => ['Data gagal dimuat', 'Silakan coba lagi. Jika masalah berlanjut, hubungi administrator.'],
    };
@endphp
<div {{ $attributes->class(['ui-feedback-state', 'ui-feedback-state--error']) }} role="alert">
    <x-ui.lucide name="alert-triangle" /><h3>{{ $title }}</h3><p>{{ $message }}</p>
    @if((string) $kind === '401')<x-ui.link variant="primary" :href="route('login')">Masuk kembali</x-ui.link>
    @elseif($retryUrl)<x-ui.link variant="secondary" :href="$retryUrl">Coba lagi</x-ui.link>@endif
    {{ $slot }}
</div>
