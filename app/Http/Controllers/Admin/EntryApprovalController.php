<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormEntry;
use App\Models\FormEntryApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\EntryStatusChangedMail;

class EntryApprovalController extends Controller
{
    /**
     * Handle approval actions for a FormEntry.
     * action: review | approve | reject
     */
    public function act(Request $r, FormEntry $entry)
    {
        // Validasi input
        $data = $r->validate([
            'action' => ['required', 'in:review,approve,reject'],
            'notes'  => ['nullable', 'string', 'max:2000'],
        ]);

        // Otorisasi (super_admin atau dept_admin departemen terkait)
        abort_unless(Gate::allows('entry-approve', $entry), 403);

        $nextAction = $data['action'];

        // Aturan transisi status
        $allowed = match ($entry->status) {
            'submitted' => ['review', 'approve', 'reject'],
            'reviewed'  => ['approve', 'reject'],
            'approved'  => [], // final
            'rejected'  => [], // final
            default     => [],
        };
        abort_unless(in_array($nextAction, $allowed, true), 422, 'Transisi status tidak diizinkan.');

        // Map action -> status enum di DB
        $map = [
            'review'  => 'reviewed',
            'approve' => 'approved',
            'reject'  => 'rejected',
        ];
        $newStatus = $map[$nextAction];

        // Update status entry
        $entry->update(['status' => $newStatus]);

        // Simpan histori approval
        FormEntryApproval::create([
            'form_entry_id' => $entry->id,
            'actor_id'      => $r->user()->id,
            'action'        => $nextAction,
            'notes'         => $data['notes'] ?? null,
        ]);

        // Kirim email notifikasi (opsional)
        try {
            if ($entry->user?->email) {
                // Jika belum pakai queue worker, ganti queue() -> send()
                Mail::to($entry->user->email)->queue(
                    new EntryStatusChangedMail($entry, $nextAction, $data['notes'] ?? null)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('EntryApprovalController email error: '.$e->getMessage());
        }

        return back()->with('ok', 'Status diperbarui ke '.strtoupper($newStatus));
    }
}
