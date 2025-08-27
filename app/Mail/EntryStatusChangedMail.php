<?php

namespace App\Mail;

use App\Models\FormEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class EntryStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var FormEntry */
    public FormEntry $entry;

    /** @var string review|approve|reject */
    public string $action;

    /** @var string|null */
    public ?string $notes;

    /**
     * Buat instance baru.
     */
    public function __construct(FormEntry $entry, string $action, ?string $notes = null)
    {
        $this->entry  = $entry->loadMissing(['form','user']);
        $this->action = $action;
        $this->notes  = $notes;
    }

    /**
     * Subjek email.
     */
    public function envelope(): Envelope
    {
        // Subject akan mencerminkan status terbaru dari entry
        // (status di model sudah di-update di controller saat aksi approval)
        $subject = 'Status Entri #' . $this->entry->id . ': ' . strtoupper($this->entry->status);

        return new Envelope(subject: $subject);
    }

    /**
     * View + data untuk email.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.entry-status',
            with: [
                'entry'  => $this->entry,
                'action' => $this->action,
                'notes'  => $this->notes,
            ],
        );
    }

    /**
     * Lampiran email (otomatis lampirkan PDF bukti jika ada).
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        $rel = $this->entry->pdf_output_path; // contoh: entries/entry-123.pdf
        if ($rel) {
            $abs = storage_path('app/public/' . $rel);
            if (is_file($abs)) {
                $attachments[] = Attachment::fromPath($abs)
                    ->as('Bukti-Entri-#' . $this->entry->id . '.pdf')
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}
