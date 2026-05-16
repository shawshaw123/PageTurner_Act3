<?php

namespace App\Mail;

use App\Models\BackupMonitoring;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupSuccessMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public BackupMonitoring $backup;

    public function __construct(BackupMonitoring $backup)
    {
        $this->backup = $backup;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "✅ Backup Success - {$this->backup->backup_type} backup completed",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.backup-success',
            with: [
                'backup' => $this->backup,
                'size' => $this->formatBytes($this->backup->size_bytes),
            ],
        );
    }

    protected function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
