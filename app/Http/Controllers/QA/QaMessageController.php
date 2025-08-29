<?php

namespace App\Http\Controllers\QA;

use App\Http\Controllers\Controller;
use App\Models\{QaThread, QaMessage};
use Illuminate\Http\Request;

class QaMessageController extends Controller
{
    /**
     * Tambahkan balasan di thread
     */
    public function store(Request $request, QaThread $thread)
    {
        $this->authorize('reply',$thread);

        $data = $request->validate([
            'body'               => ['required','string'],
            'parent_id'          => ['nullable','exists:qa_messages,id'],
            'is_official_answer' => ['sometimes','boolean'],
        ]);

        QaMessage::create([
            'thread_id'          => $thread->id,
            'user_id'            => $request->user()->id,
            'body'               => $data['body'],
            'parent_id'          => $data['parent_id'] ?? null,
            'is_official_answer' => ($request->user()->isAdmin() || $request->user()->isSuperAdmin())
                                     ? (bool)($data['is_official_answer'] ?? false)
                                     : false,
        ]);

        $thread->update(['last_message_at'=>now()]);

        return redirect()->route('admin.qa.show',$thread)->with('ok','Balasan berhasil dikirim.');
    }
}
