<?php

namespace App\Http\Controllers\QA;

use App\Http\Controllers\Controller;
use App\Models\{QaMessage, Site, User};
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
        $allowedSiteIds = $user->allowedSiteIds();
        $selectedSiteId = $request->integer('site_id') ?: session('active_site_id');

        abort_if($selectedSiteId && !$user->hasSiteAccess((int) $selectedSiteId), 403);

        $threads = QaThread::with([
                'creator',
                'site',
                'messages' => fn ($q) => $q->with('user')->latest(),
            ])
            ->withCount('messages')
            ->where(function ($q) use ($user) {
                $q->where('scope', 'public')
                    ->orWhereHas('participants', fn ($x) => $x->where('users.id', $user->id));
            })
            ->when(is_array($allowedSiteIds), fn ($q) => $q->where(function ($siteQuery) use ($allowedSiteIds) {
                $siteQuery->whereNull('site_id')->orWhereIn('site_id', $allowedSiteIds ?: [-1]);
            }))
            ->when($selectedSiteId, fn ($q) => $q->where('site_id', $selectedSiteId))
            ->latest('last_message_at')
            ->paginate(15);

        $sites = $user->isSuperAdmin()
            ? Site::query()->orderBy('code')->get(['id', 'code', 'name'])
            : $user->sites()->orderBy('code')->get(['sites.id', 'sites.code', 'sites.name']);

        return view('admin.qa.index', compact('threads', 'sites', 'selectedSiteId'));
    }

    /**
     * Khusus lihat publik
     */
    public function public(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Form buat thread baru
     */
    public function create(Request $request)
    {
        $admins = User::whereIn('role',['admin','super_admin'])
            ->orderBy('name')
            ->get(['id','name','role']);
        $sites = $request->user()->isSuperAdmin()
            ? Site::query()->orderBy('code')->get(['id', 'code', 'name'])
            : $request->user()->sites()->orderBy('code')->get(['sites.id', 'sites.code', 'sites.name']);

        $selectedSiteId = $request->integer('site_id') ?: session('active_site_id') ?: old('site_id');
        if (!$selectedSiteId && $sites->count() === 1) {
            $selectedSiteId = (int) $sites->first()->id;
        }
        $members = $selectedSiteId && $request->user()->hasSiteAccess((int) $selectedSiteId)
            ? User::whereHas('sites', fn ($q) => $q->where('sites.id', $selectedSiteId))
                ->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email', 'role'])
            : collect();

        return view('admin.qa.create', compact('admins', 'sites', 'members', 'selectedSiteId'));
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
            'recipient_ids' => ['nullable', 'array'],
            'recipient_ids.*' => ['integer', 'exists:users,id'],
            'body'         => ['required','string'],
            'site_id'      => ['required', 'integer', 'exists:sites,id'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,txt', 'max:10240'],
        ]);

        abort_unless($request->user()->hasSiteAccess((int) $data['site_id']), 403);

        $siteMemberIds = User::whereHas('sites', fn ($q) => $q->where('sites.id', $data['site_id']))
            ->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        $recipientIds = collect($data['recipient_ids'] ?? [])
            ->merge(filled($data['recipient_id'] ?? null) ? [$data['recipient_id']] : [])
            ->map(fn ($id) => (int) $id)->unique()->values();
        abort_unless($recipientIds->diff($siteMemberIds)->isEmpty(), 403);

        $thread = QaThread::create([
            'subject'         => $data['subject'],
            'scope'           => $data['scope'],
            'site_id'         => (int) $data['site_id'],
            'created_by'      => $request->user()->id,
            'status'          => 'open',
            'last_message_at' => now(),
        ]);

        // Tambahkan peserta kalau private
        if ($thread->scope === 'private') {
            $thread->participants()->attach($request->user()->id, ['role_label'=>$request->user()->role]);
            $recipients = User::whereIn('id', $recipientIds)->get();
            foreach ($recipients as $recipient) {
                $thread->participants()->syncWithoutDetaching([$recipient->id => ['role_label' => $recipient->role]]);
            }
        }

        // Pesan pertama
        $message = QaMessage::create([
            'thread_id' => $thread->id,
            'user_id'   => $request->user()->id,
            'body'      => $data['body'],
        ]);

        if ($request->hasFile('attachments')) {
            $message->update(['attachments' => collect($request->file('attachments'))->map(fn ($file) => [
                'name' => $file->getClientOriginalName(),
                'path' => $file->store("qa/{$thread->id}", 'private'),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ])->values()->all()]);
        }

        return redirect()->route('admin.qa.index', ['site_id' => $thread->site_id])->with('ok','Topik berhasil dibuat.');
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
