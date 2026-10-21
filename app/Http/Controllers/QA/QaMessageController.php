<?php

namespace App\Http\Controllers\QA;

use App\Http\Controllers\Controller;
use App\Models\{QaThread, QaMessage};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QaMessageController extends Controller
{
    /**
     * Tambahkan balasan di thread
     */
    public function store(Request $request, QaThread $thread)
    {
        $this->authorize('reply',$thread);

        $data = $request->validate([
            'body'               => ['nullable','string','max:10000'],
            'parent_id'          => ['nullable','exists:qa_messages,id'],
            'is_official_answer' => ['sometimes','boolean'],
            'attachments'        => ['nullable','array','max:5'],
            'attachments.*'      => ['file','mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,txt','max:10240'],
        ]);

        if (blank($data['body'] ?? null) && !$request->hasFile('attachments')) {
            return back()->withErrors(['body' => 'Tulis pesan atau lampirkan file terlebih dahulu.'])->withInput();
        }

        $message = QaMessage::create([
            'thread_id'          => $thread->id,
            'user_id'            => $request->user()->id,
            'body'               => $data['body'],
            'parent_id'          => $data['parent_id'] ?? null,
            'is_official_answer' => ($request->user()->isAdmin() || $request->user()->isSuperAdmin())
                                     ? (bool)($data['is_official_answer'] ?? false)
                                     : false,
        ]);

        if ($request->hasFile('attachments')) {
            $message->update(['attachments' => collect($request->file('attachments'))->map(fn ($file) => [
                'name' => $file->getClientOriginalName(),
                'path' => $file->store("qa/{$thread->id}", 'private'),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ])->values()->all()]);
        }

        $thread->update(['last_message_at'=>now()]);

        return redirect()->route('admin.qa.show',$thread)->with('ok','Balasan berhasil dikirim.');
    }

    public function downloadAttachment(Request $request, QaMessage $message, int $index)
    {
        $this->authorize('view', $message->thread);
        abort_unless($index >= 0 && $index < count($message->attachments ?? []), 404);

        $attachment = $message->attachments[$index];
        abort_unless(isset($attachment['path']) && Storage::disk('private')->exists($attachment['path']), 404);

        return Storage::disk('private')->download($attachment['path'], $attachment['name'] ?? 'lampiran');
    }
}
