<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheNotification extends Model
{
    use HasFactory;

    protected $table = 'she_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'priority',
        'link',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function notify(int $userId, string $title, string $message, string $type = 'general', string $priority = 'medium', ?string $link = null): self
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'priority' => $priority,
            'link' => $link,
            'is_read' => false,
        ]);
    }

    /**
     * Send notification to all users matching a role (e.g. Admin, Dept Head)
     */
    public static function notifyRole(string $role, string $title, string $message, string $type = 'general', string $priority = 'medium', ?string $link = null): int
    {
        $users = User::where('role', $role)
            ->orWhereIn('role', ['super_admin', 'admin_ho', 'admin_site'])
            ->get(['id']);
        $count = 0;
        foreach ($users as $user) {
            self::notify($user->id, $title, $message, $type, $priority, $link);
            $count++;
        }
        return $count;
    }

    /**
     * Scan database and generate automated reminder notifications:
     * - PICA due date H-3
     * - PICA due date H0
     * - Overdue PICA
     * - Waiting verification
     * - Document review due (H-30)
     */
    public static function generateAutomatedReminders(): array
    {
        $generated = 0;
        $today = now()->toDateString();
        $in3Days = now()->addDays(3)->toDateString();

        // 1. PICA Due Date H-3
        $picaH3 = HseCorrectiveAction::whereNotIn('status', ['Closed'])
            ->whereDate('due_date', $in3Days)
            ->whereNotNull('assigned_to')
            ->get();
        foreach ($picaH3 as $p) {
            $existing = self::where('user_id', $p->assigned_to)
                ->where('type', 'pica_due_h3')
                ->where('link', route('hse.dashboard.pica.detail', $p->id))
                ->whereDate('created_at', $today)
                ->first();
            if (!$existing) {
                self::notify(
                    userId: $p->assigned_to,
                    title: 'PICA Jatuh Tempo H-3: ' . $p->action_no,
                    message: "Tindakan korektif '{$p->title}' akan jatuh tempo pada 3 hari ke depan ({$p->due_date->format('d M Y')}). Harap segera selesaikan bukti implementasi.",
                    type: 'pica_due_h3',
                    priority: 'high',
                    link: route('hse.dashboard.pica.detail', $p->id)
                );
                $generated++;
            }
        }

        // 2. PICA Due Date H0 (Hari Ini)
        $picaH0 = HseCorrectiveAction::whereNotIn('status', ['Closed'])
            ->whereDate('due_date', $today)
            ->whereNotNull('assigned_to')
            ->get();
        foreach ($picaH0 as $p) {
            $existing = self::where('user_id', $p->assigned_to)
                ->where('type', 'pica_due_h0')
                ->where('link', route('hse.dashboard.pica.detail', $p->id))
                ->whereDate('created_at', $today)
                ->first();
            if (!$existing) {
                self::notify(
                    userId: $p->assigned_to,
                    title: 'PICA Jatuh Tempo Hari Ini (H0): ' . $p->action_no,
                    message: "PERHATIAN: Hari ini adalah batas akhir penyelesaian PICA '{$p->title}'. Harap submit bukti verifikasi sebelum melewati target.",
                    type: 'pica_due_h0',
                    priority: 'urgent',
                    link: route('hse.dashboard.pica.detail', $p->id)
                );
                $generated++;
            }
        }

        // 3. Overdue PICA
        $picaOverdue = HseCorrectiveAction::whereNotIn('status', ['Closed'])
            ->whereDate('due_date', '<', $today)
            ->whereNotNull('assigned_to')
            ->get();
        foreach ($picaOverdue as $p) {
            $existing = self::where('user_id', $p->assigned_to)
                ->where('type', 'pica_overdue')
                ->where('link', route('hse.dashboard.pica.detail', $p->id))
                ->whereDate('created_at', '>=', now()->subDays(2)->toDateString())
                ->first();
            if (!$existing) {
                self::notify(
                    userId: $p->assigned_to,
                    title: 'OVERDUE PICA: ' . $p->action_no,
                    message: "PICA '{$p->title}' telah melewati batas waktu due date ({$p->due_date->format('d M Y')}). Status: Overdue.",
                    type: 'pica_overdue',
                    priority: 'urgent',
                    link: route('hse.dashboard.pica.detail', $p->id)
                );
                $generated++;
            }
        }

        // 4. Document Review Due (H-30)
        $docReview = SheDocument::where('status', 'Published')
            ->whereDate('review_date', '<=', now()->addDays(30)->toDateString())
            ->whereNotNull('uploaded_by')
            ->get();
        foreach ($docReview as $doc) {
            $existing = self::where('user_id', $doc->uploaded_by)
                ->where('type', 'document_review_due')
                ->where('link', route('hse.dashboard.documents.show', $doc->id))
                ->whereDate('created_at', '>=', now()->subDays(7)->toDateString())
                ->first();
            if (!$existing) {
                self::notify(
                    userId: $doc->uploaded_by,
                    title: 'Jadwal Review Dokumen: ' . $doc->document_code,
                    message: "Dokumen {$doc->document_code} - {$doc->title} mendekati batas review berkala ({$doc->review_date->format('d M Y')}). Harap lakukan peninjauan.",
                    type: 'document_review_due',
                    priority: 'medium',
                    link: route('hse.dashboard.documents.show', $doc->id)
                );
                $generated++;
            }
        }

        return ['generated_count' => $generated];
    }
}
