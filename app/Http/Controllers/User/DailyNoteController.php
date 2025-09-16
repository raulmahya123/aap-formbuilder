<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DailyNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyNoteController extends Controller
{
    public function index()
    {
        $notes = DailyNote::with('user')->latest()->paginate(10);
        return view('user.daily_notes.index', compact('notes'));
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
    $data['note_time'] = now(); // selalu waktu saat simpan
    $data['title']     = 'Daily Hari Ini'; // judul fix

    DailyNote::create($data);

    return redirect()
        ->route('user.daily_notes.index')
        ->with('success', 'Catatan harian berhasil ditambahkan.');
}
}
