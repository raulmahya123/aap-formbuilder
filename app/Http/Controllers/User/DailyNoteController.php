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

        // Opsi jumlah item per halaman (default 10, max 100)
        $perPage = (int) $request->input('perPage', 10);
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 10;

        $notes = DailyNote::with('user')
            ->where('user_id', Auth::id()) // hanya catatan milik user yang login
            // hanya tampilkan catatan pada hari target (WIB)
            ->whereDate('note_time', $targetDate->toDateString())
            // opsional: pencarian isi/judul
            ->when($request->filled('q'), function ($q) use ($request) {
                $kw = trim($request->q);
                $q->where(function ($w) use ($kw) {
                    $w->where('title', 'like', "%{$kw}%")
                      ->orWhere('content', 'like', "%{$kw}%");
                });
            })
            ->orderByDesc('note_time')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('user.daily_notes.index', [
            'notes'      => $notes,
            // untuk prefill input <input type="date">
            'targetDate' => $targetDate->toDateString(),
            'perPage'    => $perPage,
            'query'      => $request->q,
        ]);
    }

    public function create()
    {
        return view('user.daily_notes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'   => ['required', 'string', 'max:255'], // user wajib isi title
            'content' => ['required', 'string'],
        ]);

        $data['user_id']   = Auth::id();
        $data['note_time'] = Carbon::now('Asia/Jakarta');

        DailyNote::create($data);

        return redirect()
            ->route('user.daily_notes.index')
            ->with('success', 'Catatan harian berhasil ditambahkan.');
    }
}
