@extends('layouts.app')

@section('title', 'Notification Center — MANDALA')

@section('content')
<div class="ui-page space-y-6">
    <header class="ui-page-header">
        <div class="ui-page-header__row">
            <div class="ui-page-header__copy">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-3 w-3 rounded-full bg-[#1646A0]"></span>
                    <h1 class="ui-page-title">Notification Center</h1>
                </div>
                <p class="ui-page-description">Pusat notifikasi tugas PICA, reminder jatuh tempo, verifikasi evidence, dokumen review, dan status approval.</p>
            </div>
            <div class="ui-page-actions flex items-center gap-2">
                @if($unreadCount > 0)
                    <form action="{{ route('hse.dashboard.notifications.markAllRead') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-[#1646A0] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#123880] transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Tandai Semua Dibaca ({{ $unreadCount }})
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </header>

    @if(session('ok'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 shadow-sm flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('ok') }}
        </div>
    @endif

    {{-- Filter Categories Summary --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Total Notifikasi</span>
                <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($notifications->total()) }}</p>
            </div>
            <span class="p-2 rounded-lg bg-blue-50 text-[#1646A0]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </span>
        </div>
        <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Belum Dibaca</span>
                <p class="text-xl font-bold text-rose-600 mt-1">{{ number_format($unreadCount) }}</p>
            </div>
            <span class="p-2 rounded-lg bg-rose-50 text-rose-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </span>
        </div>
        <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Sudah Dibaca</span>
                <p class="text-xl font-bold text-emerald-600 mt-1">{{ number_format(max(0, $notifications->total() - $unreadCount)) }}</p>
            </div>
            <span class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
        </div>
        <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Prioritas Urgent/High</span>
                <p class="text-xl font-bold text-slate-900 mt-1">Aktif</p>
            </div>
            <span class="p-2 rounded-lg bg-amber-50 text-amber-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </span>
        </div>
    </div>

    {{-- Main Notification List --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Daftar Notifikasi Anda</h3>
            <span class="text-xs text-slate-500">Klik "Buka Record" untuk langsung menuju dokumen atau tindakan terkait.</span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($notifications as $notif)
                @php
                    $isUrgent = in_array(strtolower($notif->priority), ['urgent', 'high']);
                    $priorityBadge = match(strtolower($notif->priority)) {
                        'urgent' => 'bg-rose-100 text-rose-800 border-rose-300',
                        'high' => 'bg-orange-100 text-orange-800 border-orange-300',
                        'medium' => 'bg-amber-100 text-amber-800 border-amber-300',
                        default => 'bg-slate-100 text-slate-800 border-slate-300',
                    };
                @endphp
                <div class="p-4 transition hover:bg-slate-50 {{ $notif->is_read ? 'opacity-70 bg-white' : 'bg-blue-50/20' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1.5 flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                @if(!$notif->is_read)
                                    <span class="h-2 w-2 rounded-full bg-[#1646A0] animate-pulse"></span>
                                @endif
                                <h3 class="text-sm font-bold text-slate-900">{{ $notif->title }}</h3>
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border {{ $priorityBadge }}">
                                    {{ $notif->priority }}
                                </span>
                                @if($notif->type)
                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                        {{ str_replace('_', ' ', $notif->type) }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ $notif->message }}</p>
                            <span class="block text-[11px] text-slate-400">
                                {{ $notif->created_at->diffForHumans() }} • {{ $notif->created_at->format('d M Y H:i') }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($notif->link)
                                <a href="{{ $notif->link }}" class="inline-flex items-center gap-1 rounded-lg bg-[#1646A0] px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-[#123880] transition shadow-sm">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    Buka Record
                                </a>
                            @endif

                            @if(!$notif->is_read)
                                <form action="{{ route('hse.dashboard.notifications.read', $notif->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 transition" title="Tandai sudah dibaca">
                                        ✓ Dibaca
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-slate-400">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <p class="mt-2 text-sm font-semibold text-slate-600">Tidak ada notifikasi aktif saat ini.</p>
                    <p class="text-xs text-slate-400 mt-0.5">Semua tugas PICA, persetujuan dokumen, dan reminder telah terselesaikan.</p>
                </div>
            @endforelse
        </div>

    </div>

    <div class="ui-pagination-footer">
            {{ $notifications->links() }}
        </div>
</div>
@endsection
