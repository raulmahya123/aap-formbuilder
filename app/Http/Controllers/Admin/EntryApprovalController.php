<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormEntry;
use App\Models\FormEntryApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Otorisasi (gate 'entry-approve' sudah kamu define di AuthServiceProvider)
        $this->authorize('entry-approve', $entry);

        $nextAction = $data['action'];

        // Aturan transisi status → hanya izinkan alur yang valid
        $allowed = match ($entry->status) {
            'submitted' => ['review', 'approve', 'reject'],
            'reviewed'  => ['approve', 'reject'],
            'approved'  => [], // final
            'rejected'  => [], // final
            default     => [],
        };
        abort_unless(in_array($nextAction, $allowed, true), 422, 'Transisi status tidak diizinkan.');

        // Map action -> status baru di DB
        $newStatus = match ($nextAction) {
            'review'  => 'reviewed',
            'approve' => 'approved',
            'reject'  => 'rejected',
        };

        // Simpan atomik: histori + update status
        DB::transaction(function () use ($r, $entry, $nextAction, $newStatus, $data) {
            // Histori approval
            FormEntryApproval::create([
                'form_entry_id' => $entry->id,
                'actor_id'      => $r->user()->id,
                'action'        => $nextAction,
                'notes'         => $data['notes'] ?? null,
            ]);

            // Update status entry (hindari mass assignment issues)
            $entry->status = $newStatus;
            $entry->save();
        });

        // Kirim email notifikasi (opsional)
        try {
            if ($entry->relationLoaded('user') ? $entry->user?->email : $entry->load('user')->user?->email) {
                // Jika belum pakai queue worker, ganti queue() → send()
                Mail::to($entry->user->email)->queue(
                    new EntryStatusChangedMail($entry->fresh(), $nextAction, $data['notes'] ?? null)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('EntryApprovalController email error: '.$e->getMessage());
        }

        return back()->with('ok', 'Status diperbarui ke '.strtoupper($newStatus));
    }
}
