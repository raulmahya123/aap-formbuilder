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
        $dateInput   = $request->input('date');             // opsional
        $q           = trim((string) $request->input('q', ''));
        $userId      = $request->input('user_id');          // opsional: filter penulis
        $perPageRaw  = $request->input('perPage', '25');    // 'all' atau angka
        $perPageInt  = is_numeric($perPageRaw) ? (int) $perPageRaw : null;

        if ($perPageInt !== null) {
            $perPageInt = max(1, min($perPageInt, 100));
        }

        $query = DailyNote::query()
            ->with('user:id,name')
            // (Dihapus) ->where('user_id', Auth::id())
            ->when($userId, fn($qb) => $qb->where('user_id', $userId))
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhere('content', 'like', "%{$q}%");
                });
            })
            ->when(!empty($dateInput), function ($qb) use ($dateInput) {
                $startUtc = \Carbon\Carbon::parse($dateInput, 'Asia/Jakarta')->startOfDay()->utc();
                $endUtc   = \Carbon\Carbon::parse($dateInput, 'Asia/Jakarta')->endOfDay()->utc();
                $qb->whereBetween('note_time', [$startUtc, $endUtc]);
            })
            ->orderByDesc('note_time')
            ->orderByDesc('id');

        $notes = $perPageRaw === 'all'
            ? $query->get()
            : $query->paginate($perPageInt ?? 25)->withQueryString();

        return view('user.daily_notes.index', [
            'notes'      => $notes,
            'targetDate' => $dateInput,
            'perPage'    => $perPageRaw,
            'query'      => $q,
            'userId'     => $userId,
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
