<?php

namespace App\Http\Controllers\QA;

use App\Http\Controllers\Controller;
use App\Models\{QaMessage, User};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\QaThread;

class QaThreadController extends Controller
{
    /**
     * List semua thread yang bisa user lihat
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Ambil public threads
        $public = QaThread::with('creator')
            ->where('scope', 'public');

        // Ambil private threads yang user ikut
        $private = QaThread::with('creator')
            ->where('scope', 'private')
            ->whereHas('participants', fn($q) => $q->where('users.id', $user->id));

        // Gabungkan query → unionAll tidak bisa eager load, jadi cara gampang pakai orWhereHas
        $threads = QaThread::with('creator')
            ->where(function($q) use ($user) {
                $q->where('scope', 'public')
                  ->orWhereHas('participants', fn($x)=>$x->where('users.id', $user->id));
            })
            ->latest('last_message_at')
            ->paginate(15);

        return view('admin.qa.index', compact('threads'));
    }

    /**
     * Khusus lihat publik
     */
    public function public()
    {
        $threads = QaThread::with('creator')
            ->where('scope', 'public')
            ->latest('last_message_at')
            ->paginate(15);

        return view('admin.qa.public', compact('threads'));
    }

    /**
     * Form buat thread baru
     */
    public function create()
    {
        $admins = User::whereIn('role',['admin','super_admin'])
            ->orderBy('name')
            ->get(['id','name','role']);
        return view('admin.qa.create', compact('admins'));
    }

    /**
     * Simpan thread baru
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'subject'      => ['required','string','max:200'],
            'scope'        => ['required', Rule::in(['public','private'])],
            'recipient_id' => ['nullable','exists:users,id'],
            'body'         => ['required','string'],
        ]);

        $thread = QaThread::create([
            'subject'         => $data['subject'],
            'scope'           => $data['scope'],
            'created_by'      => $request->user()->id,
            'status'          => 'open',
            'last_message_at' => now(),
        ]);

        // Tambahkan peserta kalau private
        if ($thread->scope === 'private') {
            $thread->participants()->attach($request->user()->id, ['role_label'=>$request->user()->role]);
            if ($data['recipient_id']) {
                $recipient = User::find($data['recipient_id']);
                $thread->participants()->attach($recipient->id, ['role_label'=>$recipient->role]);
            }
        }

        // Pesan pertama
        QaMessage::create([
            'thread_id' => $thread->id,
            'user_id'   => $request->user()->id,
            'body'      => $data['body'],
        ]);

        return redirect()->route('admin.qa.show',$thread)->with('ok','Thread berhasil dibuat.');
    }

    /**
     * Detail thread + daftar pesan
     */
    public function show(QaThread $thread)
    {
        $this->authorize('view',$thread);

        $messages = $thread->messages()
        ->with('user')
        ->orderBy('created_at') // lama -> baru; kalau mau terbaru di bawah, sudah benar
        ->get();
        return view('admin.qa.show', compact('thread','messages'));
    }

    /**
     * Tandai thread selesai
     */
    public function resolve(QaThread $thread)
    {
        $this->authorize('resolve',$thread);

        $thread->update(['status'=>'resolved']);
        return back()->with('ok','Thread ditandai selesai.');
    }
}
