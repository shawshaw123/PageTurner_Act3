<?php

namespace App\Mail;

use App\Models\BackupMonitoring;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupFailureMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public BackupMonitoring $backup;
    public \Exception $exception;

    public function __construct(BackupMonitoring $backup, \Exception $exception)
    {
        $this->backup = $backup;
        $this->exception = $exception;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "❌ Backup Failed - {$this->backup->backup_type} backup error",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.backup-failure',
            with: [
                'backup' => $this->backup,
                'error' => $this->exception->getMessage(),
                'trace' => $this->exception->getTraceAsString(),
            ],
        );
    }
}
