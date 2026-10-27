@extends('layouts.app')

@section('title', 'Isi Checklist Inspeksi')

@section('content')
<div class="ui-page space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $inspection->inspection_no }}</h1>
            <p class="text-sm text-slate-500">{{ $inspection->template->code }} - {{ $inspection->template->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('hse.dashboard.sap.inspeksi.show', $inspection->id) }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Lihat Hasil</a>
            <form action="{{ route('hse.dashboard.sap.inspeksi.submit', $inspection->id) }}" method="POST" onsubmit="return confirm('Ajukan inspeksi ini untuk review? Pastikan semua item telah diisi.')">
                @csrf
                <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold">Ajukan untuk Review</button>
            </form>
        </div>
    </div>

    @if(session('ok'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-800">{{ session('ok') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs text-rose-800">{{ session('error') }}</div>
    @endif

    <form action="{{ route('hse.dashboard.sap.inspeksi.save_answers', $inspection->id) }}" method="POST" class="space-y-6">
        @csrf
        @foreach($inspection->template->sections as $section)
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="text-base font-bold text-slate-900">{{ $section->title }}</h2>
                @if($section->description)<p class="text-xs text-slate-500 mt-1">{{ $section->description }}</p>@endif
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($section->items as $item)
                @php $ans = $inspection->answers->firstWhere('item_id', $item->id); @endphp
                <div class="p-6 space-y-3">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-slate-900">{{ $loop->parent->iteration }}.{{ $loop->iteration }}. {{ $item->question }}</p>
                            @if($item->guidance)<p class="text-xs text-slate-500 mt-1">{{ $item->guidance }}</p>@endif
                            @if($item->is_critical)<span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] bg-rose-100 text-rose-700 font-semibold">KRITIS</span>@endif
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        @if($item->answer_type === 'score_1_4')
                            @foreach([1,2,3,4] as $v)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" name="answers[{{ $item->id }}]" value="{{ $v }}" @checked($ans && $ans->answer == $v) required>
                                <span>{{ $v }}</span>
                            </label>
                            @endforeach
                            <span class="text-xs text-slate-400">1=Tidak Sesuai, 2=Kurang, 3=Sesuai, 4=Sangat Baik</span>
                        @elseif(in_array($item->answer_type, ['yes_no', 'yes_no_na']))
                            <label class="flex items-center gap-2 text-sm"><input type="radio" name="answers[{{ $item->id }}]" value="YA" @checked($ans && $ans->answer == 'YA') required> Ya</label>
                            <label class="flex items-center gap-2 text-sm"><input type="radio" name="answers[{{ $item->id }}]" value="TIDAK" @checked($ans && $ans->answer == 'TIDAK') required> Tidak</label>
                            @if($item->answer_type === 'yes_no_na')
                            <label class="flex items-center gap-2 text-sm"><input type="radio" name="answers[{{ $item->id }}]" value="NA" @checked($ans && $ans->answer == 'NA')> N/A</label>
                            @endif
                        @else
                            <input type="text" name="answers[{{ $item->id }}]" class="w-full max-w-md text-sm rounded-lg border border-slate-300 p-2" value="{{ $ans->answer ?? '' }}" placeholder="Uraian...">
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan / Bukti Temuan</label>
                        <textarea name="remarks[{{ $item->id }}]" rows="2" class="w-full text-sm rounded-lg border border-slate-300 p-2">{{ $ans->remarks ?? '' }}</textarea>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        <div class="flex justify-end gap-2">
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-slate-900 text-white text-sm font-semibold">Simpan Jawaban</button>
        </div>
    </form>
</div>
@endsection
