<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DailyNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DailyNoteController extends Controller
{
    public function index(Request $request)
    {
        // Tentukan tanggal target: ?date=YYYY-MM-DD, jika kosong → hari ini (WIB)
        $targetDate = $request->date
            ? Carbon::parse($request->date, 'Asia/Jakarta')->startOfDay()
            : Carbon::now('Asia/Jakarta')->startOfDay();

        $notes = DailyNote::with('user')
            // hanya tampilkan catatan pada hari target (WIB)
            ->whereDate('note_time', $targetDate->toDateString())
            // opsional: pencarian isi/judul
            ->when($request->filled('q'), function ($q) use ($request) {
                $kw = $request->q;
                $q->where(function ($w) use ($kw) {
                    $w->where('title', 'like', "%{$kw}%")
                      ->orWhere('content', 'like', "%{$kw}%");
                });
            })
            ->orderByDesc('note_time')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('user.daily_notes.index', [
            'notes'      => $notes,
            // untuk prefill input <input type="date">
            'targetDate' => $targetDate->toDateString(),
        ]);
    }

    public function create()
    {
        return view('user.daily_notes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'content' => ['required', 'string'], // title dihapus dari validasi
        ]);

        $data['user_id']   = auth()->id();
        $data['note_time'] = Carbon::now('Asia/Jakarta'); // simpan WIB
        $data['title']     = 'Daily Hari Ini';            // judul fix

        DailyNote::create($data);

        return redirect()
            ->route('user.daily_notes.index')
            ->with('success', 'Catatan harian berhasil ditambahkan.');
    }
}
