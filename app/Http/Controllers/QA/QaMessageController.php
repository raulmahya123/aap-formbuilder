<?php

namespace App\Http\Controllers\QA;

use App\Http\Controllers\Controller;
use App\Models\{ChatMessageReaction, ChatReport, ChatReportStatusHistory, QaMessage, QaThread, Site, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class QaMessageController extends Controller
{
    /**
     * Kirim Pesan Baru (Text / Lampiran / Reply)
     */
    public function store(Request $request, QaThread $thread)
    {
        $this->authorize('reply', $thread);

        $data = $request->validate([
            'body'               => ['nullable', 'string', 'max:10000'],
            'parent_id'          => ['nullable', 'exists:qa_messages,id'],
            'forwarded_from_id'  => ['nullable', 'exists:users,id'],
            'is_official_answer' => ['sometimes', 'boolean'],
            'attachments'        => ['nullable', 'array', 'max:10'],
            'attachments.*'      => ['file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,txt,webp,zip', 'max:15360'],
        ]);

        if (blank($data['body'] ?? null) && !$request->hasFile('attachments')) {
            return response()->json([
                'success' => false,
                'message' => 'Pesan tidak boleh kosong jika tanpa lampiran.',
            ], 422);
        }

        $user = $request->user();

        $message = null;
        DB::transaction(function () use (&$message, $thread, $user, $data, $request) {
            $message = QaMessage::create([
                'thread_id'          => $thread->id,
                'user_id'            => $user->id,
                'body'               => $data['body'] ?? '',
                'message_type'       => 'text',
                'parent_id'          => $data['parent_id'] ?? null,
                'forwarded_from_id'  => $data['forwarded_from_id'] ?? null,
                'is_official_answer' => ($user->isAdmin() || $user->isSuperAdmin())
                    ? (bool) ($data['is_official_answer'] ?? false)
                    : false,
            ]);

            if ($request->hasFile('attachments')) {
                $attachments = collect($request->file('attachments'))->map(fn ($file) => [
                    'name' => $file->getClientOriginalName(),
                    'path' => $file->store("qa/{$thread->id}", 'private'),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ])->values()->all();

                $message->update(['attachments' => $attachments]);
            }

            $thread->update(['last_message_at' => now()]);

            // Update user read pointer
            $thread->participants()->updateExistingPivot($user->id, ['last_read_at' => now()]);
        });

        $message->load(['user:id,name,role,avatar_path', 'parent.user:id,name', 'reactions']);

        return response()->json([
            'success' => true,
            'message' => [
                'id'                 => $message->id,
                'user_id'            => $message->user_id,
                'user_name'          => $message->user?->name,
                'user_avatar'        => $message->user?->avatar_path ? asset('storage/' . $message->user->avatar_path) : null,
                'user_initials'      => strtoupper(substr($message->user?->name ?? 'U', 0, 2)),
                'is_mine'            => true,
                'body'               => $message->body,
                'message_type'       => $message->message_type,
                'is_official_answer' => (bool) $message->is_official_answer,
                'attachments'        => collect($message->attachments ?? [])->map(fn ($att, $idx) => [
                    'index'    => $idx,
                    'name'     => $att['name'] ?? 'Lampiran',
                    'size'     => isset($att['size']) ? round($att['size'] / 1024, 1) . ' KB' : null,
                    'mime'     => $att['mime'] ?? '',
                    'url'      => route('admin.qa.messages.attachments.download', [$message, $idx]),
                    'is_image' => str_starts_with($att['mime'] ?? '', 'image/'),
                ])->all(),
                'parent'             => $message->parent ? [
                    'id'        => $message->parent->id,
                    'user_name' => $message->parent->user?->name,
                    'body'      => \Illuminate\Support\Str::limit($message->parent->body, 60),
                ] : null,
                'reactions'          => [],
                'is_edited'          => false,
                'is_pinned'          => false,
                'created_at'         => $message->created_at->format('H:i'),
                'can_edit'           => true,
                'can_delete'         => true,
            ],
        ]);
    }

    /**
     * Buat Laporan Terstruktur (Reporting Conversation)
     */
    public function createReport(Request $request, QaThread $thread)
    {
        $this->authorize('reply', $thread);

        $data = $request->validate([
            'title'          => ['required', 'string', 'max:200'],
            'report_type'    => ['required', 'string', 'max:50'],
            'priority'       => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'site_id'        => ['nullable', 'integer', 'exists:sites,id'],
            'department_id'  => ['nullable', 'integer', 'exists:departments,id'],
            'assigned_to'    => ['nullable', 'integer', 'exists:users,id'],
            'description'    => ['required', 'string', 'max:5000'],
            'attachments'    => ['nullable', 'array', 'max:10'],
            'attachments.*'  => ['file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,txt,webp', 'max:15360'],
        ]);

        $user = $request->user();
        $message = null;
        $report = null;

        DB::transaction(function () use (&$message, &$report, $thread, $user, $data, $request) {
            // Buat pesan khusus tipe report
            $message = QaMessage::create([
                'thread_id'    => $thread->id,
                'user_id'      => $user->id,
                'body'         => $data['title'],
                'message_type' => 'report',
            ]);

            if ($request->hasFile('attachments')) {
                $attachments = collect($request->file('attachments'))->map(fn ($file) => [
                    'name' => $file->getClientOriginalName(),
                    'path' => $file->store("qa/{$thread->id}", 'private'),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ])->values()->all();

                $message->update(['attachments' => $attachments]);
            }

            // Buat record ChatReport
            $report = ChatReport::create([
                'thread_id'     => $thread->id,
                'message_id'    => $message->id,
                'user_id'       => $user->id,
                'title'         => $data['title'],
                'report_type'   => $data['report_type'],
                'priority'      => $data['priority'],
                'site_id'       => $data['site_id'] ?? $thread->site_id,
                'department_id' => $data['department_id'] ?? $thread->department_id,
                'assigned_to'   => $data['assigned_to'] ?? null,
                'description'   => $data['description'],
                'status'        => 'open',
            ]);

            // Catat history inisiasi status
            ChatReportStatusHistory::create([
                'report_id'   => $report->id,
                'user_id'     => $user->id,
                'from_status' => 'created',
                'to_status'   => 'open',
                'notes'       => 'Laporan dibuat.',
            ]);

            $thread->update(['last_message_at' => now()]);
            $thread->participants()->updateExistingPivot($user->id, ['last_read_at' => now()]);
        });

        return response()->json([
            'success' => true,
            'report'  => $report,
        ]);
    }

    /**
     * Update Status Laporan (Open, In Progress, Resolved, Closed)
     */
    public function updateReportStatus(Request $request, ChatReport $report)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved', 'closed'])],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $oldStatus = $report->status;
        $newStatus = $data['status'];

        if ($oldStatus === $newStatus) {
            return response()->json(['success' => true]);
        }

        DB::transaction(function () use ($report, $user, $oldStatus, $newStatus, $data) {
            $report->update(['status' => $newStatus]);

            ChatReportStatusHistory::create([
                'report_id'   => $report->id,
                'user_id'     => $user->id,
                'from_status' => $oldStatus,
                'to_status'   => $newStatus,
                'notes'       => $data['notes'] ?? null,
            ]);

            // Kirim notifikasi sistem di thread
            QaMessage::create([
                'thread_id'    => $report->thread_id,
                'user_id'      => $user->id,
                'body'         => "{$user->name} memperbarui status laporan \"{$report->title}\" menjadi " . strtoupper(str_replace('_', ' ', $newStatus)),
                'message_type' => 'system',
            ]);
        });

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
        ]);
    }

    /**
     * Edit Pesan
     */
    public function update(Request $request, QaMessage $message)
    {
        $this->authorize('update', $message);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $message->update([
            'body'      => $data['body'],
            'edited_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'body'    => $message->body,
        ]);
    }

    /**
     * Hapus Pesan
     */
    public function destroy(Request $request, QaMessage $message)
    {
        $this->authorize('delete', $message);

        $message->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Toggle Pin Pesan
     */
    public function togglePin(Request $request, QaMessage $message)
    {
        $this->authorize('pin', $message);

        $isPinned = !is_null($message->pinned_at);

        $message->update([
            'pinned_at' => $isPinned ? null : now(),
            'pinned_by' => $isPinned ? null : $request->user()->id,
        ]);

        return response()->json([
            'success'   => true,
            'is_pinned' => !$isPinned,
        ]);
    }

    /**
     * Reaksi Emoji (Like, Heart, dll)
     */
    public function toggleReaction(Request $request, QaMessage $message)
    {
        $data = $request->validate([
            'reaction' => ['required', 'string', 'max:32'],
        ]);

        $user = $request->user();
        $existing = ChatMessageReaction::where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->where('reaction', $data['reaction'])
            ->first();

        if ($existing) {
            $existing->delete();
            $added = false;
        } else {
            ChatMessageReaction::create([
                'message_id' => $message->id,
                'user_id'    => $user->id,
                'reaction'   => $data['reaction'],
            ]);
            $added = true;
        }

        return response()->json([
            'success' => true,
            'added'   => $added,
        ]);
    }

    /**
     * Download Attachment
     */
    public function downloadAttachment(Request $request, QaMessage $message, int $index)
    {
        $this->authorize('view', $message->thread);
        abort_unless($index >= 0 && $index < count($message->attachments ?? []), 404);

        $attachment = $message->attachments[$index];
        abort_unless(isset($attachment['path']) && Storage::disk('private')->exists($attachment['path']), 404);

        return Storage::disk('private')->download($attachment['path'], $attachment['name'] ?? 'lampiran');
    }
}
