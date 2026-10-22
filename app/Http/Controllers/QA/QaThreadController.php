<?php

namespace App\Http\Controllers\QA;

use App\Http\Controllers\Controller;
use App\Models\{ChatReport, ChatReportStatusHistory, Department, QaMessage, QaThread, Site, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QaThreadController extends Controller
{
    /**
     * Halaman Utama Mandala Chat
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $allowedSiteIds = $user->allowedSiteIds();
        $selectedSiteId = \App\Support\SiteSelection::id($request);

        abort_if($selectedSiteId && !$user->hasSiteAccess((int) $selectedSiteId), 403);

        $sites = $user->isSuperAdmin()
            ? Site::query()->orderBy('code')->get(['id', 'code', 'name', 'company_id'])
            : $user->sites()->orderBy('code')->get(['sites.id', 'sites.code', 'sites.name', 'sites.company_id']);

        $activeThreadId = $request->integer('thread');

        return view('admin.qa.index', compact('sites', 'selectedSiteId', 'activeThreadId'));
    }

    /**
     * API: Ambil daftar percakapan (Conversation Sidebar)
     */
    public function conversations(Request $request)
    {
        $user = $request->user();
        $allowedSiteIds = $user->allowedSiteIds();
        $tab = $request->string('tab', 'all')->toString(); // all, personal, group, unread
        $query = $request->string('q')->trim()->toString();
        $siteId = $request->integer('site_id');

        $threadsQuery = QaThread::with([
                'creator:id,name,role,avatar_path',
                'site:id,code,name',
                'participants' => function ($q) {
                    $q->select('users.id', 'users.name', 'users.role', 'users.avatar_path', 'users.department_id')
                      ->withPivot(['chat_role', 'is_muted', 'is_pinned', 'pinned_at', 'is_archived', 'last_read_at']);
                },
                'latestMessage.user:id,name',
            ])
            ->where(function ($q) use ($user) {
                // Personal: hanya partisipan
                $q->where(function ($p) use ($user) {
                    $p->where('type', 'personal')
                      ->whereHas('participants', fn ($x) => $x->where('users.id', $user->id));
                })
                // Public general site: siapa saja yang punya akses ke site
                ->orWhere(function ($s) use ($user) {
                    $s->where('type', 'site')
                      ->where('scope', 'public');
                })
                // Groups: partisipan atau public
                ->orWhere(function ($g) use ($user) {
                    $g->where('type', 'group')
                      ->where(function ($sub) use ($user) {
                          $sub->where('scope', 'public')
                              ->orWhereHas('participants', fn ($x) => $x->where('users.id', $user->id));
                      });
                });
            })
            ->when(is_array($allowedSiteIds), fn ($q) => $q->where(function ($siteQuery) use ($allowedSiteIds) {
                $siteQuery->where('type', 'personal')
                          ->orWhereNull('site_id')
                          ->orWhereIn('site_id', $allowedSiteIds ?: [-1]);
            }))
            ->when($siteId > 0 && $user->hasSiteAccess($siteId), fn ($q) => $q->where(function ($sub) use ($siteId) {
                $sub->where('type', 'personal')->orWhere('site_id', $siteId);
            }));

        // Filter tab
        if ($tab === 'personal') {
            $threadsQuery->where('type', 'personal');
        } elseif ($tab === 'group') {
            $threadsQuery->whereIn('type', ['group', 'site']);
        }

        // Search query
        if ($query !== '') {
            $threadsQuery->where(function ($q) use ($query) {
                $q->where('subject', 'like', "%{$query}%")
                  ->orWhereHas('participants', fn ($p) => $p->where('users.name', 'like', "%{$query}%"))
                  ->orWhereHas('site', fn ($s) => $s->where('name', 'like', "%{$query}%")->orWhere('code', 'like', "%{$query}%"))
                  ->orWhereHas('messages', fn ($m) => $m->where('body', 'like', "%{$query}%"));
            });
        }

        $allThreads = $threadsQuery->get();

        // Transform data dan hitung unread counter
        $result = $allThreads->map(function ($thread) use ($user) {
            $myParticipant = $thread->participants->firstWhere('id', $user->id);
            $lastReadAt = $myParticipant?->pivot?->last_read_at;
            $isPinned = (bool) ($myParticipant?->pivot?->is_pinned ?? false);
            $isMuted = (bool) ($myParticipant?->pivot?->is_muted ?? false);

            $unreadCount = $thread->messages()
                ->where('user_id', '!=', $user->id)
                ->when($lastReadAt, fn ($q) => $q->where('created_at', '>', $lastReadAt))
                ->count();

            // Nama & avatar untuk personal chat
            $displayName = $thread->subject;
            $displayAvatar = $thread->avatar_path;
            $otherParticipant = null;

            if ($thread->type === 'personal') {
                $otherParticipant = $thread->participants->firstWhere('id', '!=', $user->id) ?: $thread->creator;
                if ($otherParticipant) {
                    $displayName = $otherParticipant->name;
                    $displayAvatar = $otherParticipant->avatar_path;
                }
            }

            return [
                'id'              => $thread->id,
                'subject'         => $displayName,
                'type'            => $thread->type ?: 'group',
                'group_type'      => $thread->group_type,
                'scope'           => $thread->scope,
                'site'            => $thread->site ? ['id' => $thread->site->id, 'code' => $thread->site->code, 'name' => $thread->site->name] : null,
                'is_pinned'       => $isPinned,
                'is_muted'        => $isMuted,
                'unread_count'    => $unreadCount,
                'last_message'    => $thread->latestMessage ? [
                    'id'          => $thread->latestMessage->id,
                    'user_id'     => $thread->latestMessage->user_id,
                    'user_name'   => $thread->latestMessage->user?->name ?? 'User',
                    'body'        => $thread->latestMessage->message_type === 'report' ? '📋 [Laporan] ' . \Illuminate\Support\Str::limit($thread->latestMessage->body, 30) : $thread->latestMessage->body,
                    'created_at'  => $thread->latestMessage->created_at?->diffForHumans(null, true, true),
                    'timestamp'   => $thread->latestMessage->created_at?->toIso8601String(),
                ] : null,
                'last_message_at' => $thread->last_message_at?->toIso8601String(),
                'avatar_url'      => $displayAvatar ? asset('storage/' . $displayAvatar) : null,
                'initials'        => strtoupper(substr($displayName, 0, 2)),
                'other_user'      => $otherParticipant ? [
                    'id'    => $otherParticipant->id,
                    'name'  => $otherParticipant->name,
                    'role'  => $otherParticipant->role,
                ] : null,
            ];
        });

        // Filter unread tab
        if ($tab === 'unread') {
            $result = $result->filter(fn ($item) => $item['unread_count'] > 0)->values();
        }

        // Urutan: 1. Pinned conversations, 2. last_message_at desc
        $sorted = $result->sort(function ($a, $b) {
            if ($a['is_pinned'] !== $b['is_pinned']) {
                return $a['is_pinned'] ? -1 : 1;
            }
            return strcmp($b['last_message_at'] ?? '', $a['last_message_at'] ?? '');
        })->values();

        return response()->json([
            'success' => true,
            'data'    => $sorted,
        ]);
    }

    /**
     * API: Ambil detail percakapan & riwayat pesan
     */
    public function showConversation(Request $request, QaThread $thread)
    {
        if ($thread->type === 'personal') {
            abort_unless($thread->participants()->where('users.id', $request->user()->id)->exists(), 403);
        }
        $this->authorize('view', $thread);
        $user = $request->user();

        // Update read pointer untuk user ini
        $participant = $thread->participants()->where('users.id', $user->id)->first();
        if ($participant) {
            $thread->participants()->updateExistingPivot($user->id, ['last_read_at' => now()]);
        }

        $thread->load([
            'site:id,code,name',
            'department:id,name',
            'company:id,code,name',
            'creator:id,name,role',
            'participants' => function ($q) {
                $q->select('users.id', 'users.name', 'users.email', 'users.role', 'users.avatar_path', 'users.phone', 'users.department_id')
                  ->withPivot(['chat_role', 'is_muted', 'is_pinned', 'last_read_at']);
            },
            'pinnedMessages.user:id,name',
        ]);

        $beforeId = $request->integer('before_id');

        $messagesQuery = $thread->messages()
            ->with([
                'user:id,name,role,avatar_path',
                'parent:id,user_id,body',
                'parent.user:id,name',
                'reactions.user:id,name',
                'report.site:id,code,name',
                'report.department:id,name',
                'report.assignee:id,name',
                'report.statusHistories.user:id,name',
            ])
            ->when($beforeId > 0, fn ($q) => $q->where('id', '<', $beforeId))
            ->orderBy('id', 'desc')
            ->limit(40);

        $messages = $messagesQuery->get()->reverse()->values();

        // Transform pesan
        $transformedMessages = $messages->map(function ($msg) use ($user) {
            $isMine = $msg->user_id === $user->id;
            return [
                'id'                 => $msg->id,
                'user_id'            => $msg->user_id,
                'user_name'          => $msg->user?->name ?? 'User',
                'user_role'          => $msg->user?->role,
                'user_avatar'        => $msg->user?->avatar_path ? asset('storage/' . $msg->user->avatar_path) : null,
                'user_initials'      => strtoupper(substr($msg->user?->name ?? 'U', 0, 2)),
                'is_mine'            => $isMine,
                'body'               => $msg->body,
                'message_type'       => $msg->message_type,
                'is_official_answer' => (bool) $msg->is_official_answer,
                'attachments'        => collect($msg->attachments ?? [])->map(function ($att, $idx) use ($msg) {
                    return [
                        'index' => $idx,
                        'name'  => $att['name'] ?? 'Lampiran',
                        'size'  => isset($att['size']) ? round($att['size'] / 1024, 1) . ' KB' : null,
                        'mime'  => $att['mime'] ?? '',
                        'url'   => route('admin.qa.messages.attachments.download', [$msg, $idx]),
                        'is_image' => str_starts_with($att['mime'] ?? '', 'image/'),
                    ];
                })->all(),
                'parent'             => $msg->parent ? [
                    'id'        => $msg->parent->id,
                    'user_name' => $msg->parent->user?->name ?? 'User',
                    'body'      => \Illuminate\Support\Str::limit($msg->parent->body, 60),
                ] : null,
                'report'             => $msg->report ? [
                    'id'              => $msg->report->id,
                    'title'           => $msg->report->title,
                    'report_type'     => $msg->report->report_type,
                    'priority'        => $msg->report->priority,
                    'status'          => $msg->report->status,
                    'description'     => $msg->report->description,
                    'site_code'       => $msg->report->site?->code,
                    'department_name' => $msg->report->department?->name,
                    'assignee_name'   => $msg->report->assignee?->name,
                    'histories'       => $msg->report->statusHistories->map(fn ($h) => [
                        'from'  => $h->from_status,
                        'to'    => $h->to_status,
                        'user'  => $h->user?->name,
                        'time'  => $h->created_at->format('d M H:i'),
                        'notes' => $h->notes,
                    ]),
                ] : null,
                'reactions'          => $msg->reactions->groupBy('reaction')->map(fn ($rx, $emoji) => [
                    'emoji'     => $emoji,
                    'count'     => $rx->count(),
                    'has_mine'  => $rx->contains('user_id', $user->id),
                    'users'     => $rx->pluck('user.name')->take(5)->all(),
                ])->values()->all(),
                'is_edited'          => !is_null($msg->edited_at),
                'is_pinned'          => !is_null($msg->pinned_at),
                'created_at'         => $msg->created_at->format('H:i'),
                'created_date'       => $msg->created_at->format('Y-m-d'),
                'created_date_label' => $msg->created_at->isoFormat('dddd, D MMMM Y'),
                'can_edit'           => $user->can('update', $msg),
                'can_delete'         => $user->can('delete', $msg),
            ];
        });

        // Tentukan info personal atau grup
        $displayName = $thread->subject;
        $otherUser = null;
        if ($thread->type === 'personal') {
            $otherParticipant = $thread->participants->firstWhere('id', '!=', $user->id) ?: $thread->creator;
            if ($otherParticipant) {
                $displayName = $otherParticipant->name;
                $otherUser = [
                    'id'          => $otherParticipant->id,
                    'name'        => $otherParticipant->name,
                    'email'       => $otherParticipant->email,
                    'role'        => $otherParticipant->role,
                    'avatar_url'  => $otherParticipant->avatar_path ? asset('storage/' . $otherParticipant->avatar_path) : null,
                    'phone'       => $otherParticipant->phone,
                    'site'        => $thread->site?->displayLabel() ?? '-',
                    'department'  => $otherParticipant->department_id ? Department::find($otherParticipant->department_id)?->name : '-',
                ];
            }
        }

        $myParticipant = $thread->participants->firstWhere('id', $user->id);

        return response()->json([
            'success' => true,
            'thread'  => [
                'id'            => $thread->id,
                'subject'       => $displayName,
                'description'   => $thread->description,
                'type'          => $thread->type,
                'group_type'    => $thread->group_type,
                'status'        => $thread->status,
                'site'          => $thread->site ? ['id' => $thread->site->id, 'code' => $thread->site->code, 'name' => $thread->site->name] : null,
                'avatar_url'    => ($thread->type === 'personal' && $otherUser) ? $otherUser['avatar_url'] : ($thread->avatar_path ? asset('storage/' . $thread->avatar_path) : null),
                'initials'      => strtoupper(substr($displayName, 0, 2)),
                'department'    => $thread->department ? ['id' => $thread->department->id, 'name' => $thread->department->name] : null,
                'company'       => $thread->company ? ['id' => $thread->company->id, 'name' => $thread->company->name] : null,
                'member_count'  => $thread->participants->count(),
                'is_pinned'     => (bool) ($myParticipant?->pivot?->is_pinned ?? false),
                'is_muted'      => (bool) ($myParticipant?->pivot?->is_muted ?? false),
                'can_manage'    => $user->can('manage', $thread),
                'members'       => $thread->participants->map(fn ($m) => [
                    'id'        => $m->id,
                    'name'      => $m->name,
                    'role'      => $m->role,
                    'chat_role' => $m->pivot->chat_role ?? 'member',
                    'avatar'    => $m->avatar_path ? asset('storage/' . $m->avatar_path) : null,
                ]),
                'pinned_messages' => $thread->pinnedMessages->map(fn ($p) => [
                    'id'        => $p->id,
                    'user_name' => $p->user?->name ?? 'User',
                    'body'      => \Illuminate\Support\Str::limit($p->body, 50),
                ]),
                'other_user'    => $otherUser,
            ],
            'messages' => $transformedMessages,
        ]);
    }

    /**
     * API: Buat atau temukan Personal Chat (1-on-1)
     */
    public function startPersonalChat(Request $request)
    {
        $data = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id', Rule::notIn([$request->user()->id])],
        ]);

        $user = $request->user();
        $recipient = User::findOrFail($data['recipient_id']);

        // Check hash 1-1
        $directHash = QaThread::makeDirectHash($user->id, $recipient->id);

        $thread = QaThread::where('direct_hash', $directHash)->first();

        if (!$thread) {
            DB::transaction(function () use (&$thread, $user, $recipient, $directHash) {
                $thread = QaThread::create([
                    'subject'         => $recipient->name,
                    'type'            => 'personal',
                    'scope'           => 'private',
                    'direct_hash'     => $directHash,
                    'site_id'         => null,
                    'created_by'      => $user->id,
                    'status'          => 'open',
                    'last_message_at' => now(),
                ]);

                $thread->participants()->attach([
                    $user->id      => ['role_label' => $user->chatRoleLabel(), 'chat_role' => 'member', 'last_read_at' => now()],
                    $recipient->id => ['role_label' => $recipient->chatRoleLabel(), 'chat_role' => 'member', 'last_read_at' => null],
                ]);
            });
        }

        return response()->json([
            'success'   => true,
            'thread_id' => $thread->id,
        ]);
    }

    /**
     * API: Buat Grup Baru
     */
    public function createGroup(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:150'],
            'description'   => ['nullable', 'string', 'max:500'],
            'group_type'    => ['required', Rule::in(['site', 'department', 'project', 'general'])],
            'site_id'       => ['nullable', 'integer', 'exists:sites,id'],
            'member_ids'    => ['required', 'array', 'min:1'],
            'member_ids.*'  => ['integer', 'exists:users,id'],
            'avatar'        => ['nullable', 'image', 'max:2048'],
        ]);

        if (filled($data['site_id'])) {
            abort_unless($user->hasSiteAccess((int) $data['site_id']), 403, 'Akses site ditolak.');
        }

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('chat/avatars', 'public');
        }

        $thread = null;

        DB::transaction(function () use (&$thread, $data, $user, $avatarPath) {
            $thread = QaThread::create([
                'subject'         => $data['name'],
                'type'            => 'group',
                'group_type'      => $data['group_type'],
                'description'     => $data['description'] ?? null,
                'avatar_path'     => $avatarPath,
                'site_id'         => $data['site_id'] ?? null,
                'company_id'      => $data['site_id'] ? Site::find($data['site_id'])?->company_id : null,
                'scope'           => 'private',
                'created_by'      => $user->id,
                'status'          => 'open',
                'last_message_at' => now(),
            ]);

            // Pembuat sebagai owner
            $thread->participants()->attach($user->id, [
                'role_label'   => $user->chatRoleLabel(),
                'chat_role'    => 'owner',
                'last_read_at' => now(),
            ]);

            // Anggota lainnya
            $memberIds = collect($data['member_ids'])->reject(fn ($id) => $id == $user->id)->unique();
            $members = User::whereIn('id', $memberIds)->get();

            foreach ($members as $m) {
                $thread->participants()->syncWithoutDetaching([
                    $m->id => [
                        'role_label' => $m->chatRoleLabel(),
                        'chat_role'  => 'member',
                    ],
                ]);
            }

            // System message: Grup dibuat
            QaMessage::create([
                'thread_id'    => $thread->id,
                'user_id'      => $user->id,
                'body'         => "{$user->name} membuat grup \"{$thread->subject}\"",
                'message_type' => 'system',
            ]);
        });

        return response()->json([
            'success'   => true,
            'thread_id' => $thread->id,
            'message'   => 'Grup berhasil dibuat',
        ]);
    }

    /**
     * API: Tambah / Hapus Anggota Grup
     */
    public function updateMembers(Request $request, QaThread $thread)
    {
        $this->authorize('manage', $thread);

        $data = $request->validate([
            'action'  => ['required', Rule::in(['add', 'remove', 'role'])],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role'    => ['nullable', Rule::in(['member', 'admin'])],
        ]);

        $targetUser = User::findOrFail($data['user_id']);

        if ($data['action'] === 'add') {
            $thread->participants()->syncWithoutDetaching([
                $targetUser->id => [
                    'role_label' => $targetUser->chatRoleLabel(),
                    'chat_role'  => 'member',
                ],
            ]);
            QaMessage::create([
                'thread_id'    => $thread->id,
                'user_id'      => $request->user()->id,
                'body'         => "{$request->user()->name} menambahkan {$targetUser->name} ke dalam grup",
                'message_type' => 'system',
            ]);
        } elseif ($data['action'] === 'remove') {
            $thread->participants()->detach($targetUser->id);
            QaMessage::create([
                'thread_id'    => $thread->id,
                'user_id'      => $request->user()->id,
                'body'         => "{$request->user()->name} mengeluarkan {$targetUser->name} dari grup",
                'message_type' => 'system',
            ]);
        } elseif ($data['action'] === 'role' && filled($data['role'])) {
            $thread->participants()->updateExistingPivot($targetUser->id, [
                'chat_role' => $data['role'],
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * API: Keluar dari Grup
     */
    public function leaveGroup(Request $request, QaThread $thread)
    {
        abort_if($thread->type === 'personal', 400, 'Tidak dapat keluar dari personal chat');

        $user = $request->user();
        $thread->participants()->detach($user->id);

        QaMessage::create([
            'thread_id'    => $thread->id,
            'user_id'      => $user->id,
            'body'         => "{$user->name} keluar dari grup",
            'message_type' => 'system',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * API: Pin / Mute Conversation
     */
    public function toggleConversationSetting(Request $request, QaThread $thread)
    {
        $user = $request->user();
        $data = $request->validate([
            'action' => ['required', Rule::in(['pin', 'unpin', 'mute', 'unmute', 'read'])],
        ]);

        $participant = $thread->participants()->where('users.id', $user->id)->first();
        if (!$participant) {
            // Jika belum ada di pivot (misal public channel), attach dulu
            $thread->participants()->attach($user->id, ['role_label' => $user->chatRoleLabel()]);
        }

        if ($data['action'] === 'pin') {
            $thread->participants()->updateExistingPivot($user->id, ['is_pinned' => true, 'pinned_at' => now()]);
        } elseif ($data['action'] === 'unpin') {
            $thread->participants()->updateExistingPivot($user->id, ['is_pinned' => false, 'pinned_at' => null]);
        } elseif ($data['action'] === 'mute') {
            $thread->participants()->updateExistingPivot($user->id, ['is_muted' => true]);
        } elseif ($data['action'] === 'unmute') {
            $thread->participants()->updateExistingPivot($user->id, ['is_muted' => false]);
        } elseif ($data['action'] === 'read') {
            $thread->participants()->updateExistingPivot($user->id, ['last_read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * API: Ambil daftar karyawan untuk modal New Chat / New Group
     */
    public function searchEmployees(Request $request)
    {
        $user = $request->user();
        $q = $request->string('q')->trim()->toString();
        $siteId = $request->integer('site_id');

        $users = User::active()
            ->where('id', '!=', $user->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('nik', 'like', "%{$q}%");
                });
            })
            ->when($siteId > 0, function ($query) use ($siteId) {
                $query->whereHas('sites', fn ($s) => $s->where('sites.id', $siteId));
            })
            ->with(['departments:id,name', 'sites:id,code,name'])
            ->limit(30)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $users->map(fn ($u) => [
                'id'          => $u->id,
                'name'        => $u->name,
                'email'       => $u->email,
                'role'        => $u->role,
                'nik'         => $u->nik,
                'avatar_url'  => $u->avatar_path ? asset('storage/' . $u->avatar_path) : null,
                'initials'    => strtoupper(substr($u->name, 0, 2)),
                'departments' => $u->departments->pluck('name')->join(', '),
                'sites'       => $u->sites->pluck('code')->join(', '),
            ]),
        ]);
    }
}
