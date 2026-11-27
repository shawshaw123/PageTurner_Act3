<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySalesReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $stats;
    public string $reportPath;
    public \Carbon\Carbon $date;

    public function __construct(array $stats, string $reportPath, \Carbon\Carbon $date)
    {
        $this->stats = $stats;
        $this->reportPath = $reportPath;
        $this->date = $date;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "📊 Daily Sales Report - {$this->date->format('M j, Y')}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.daily-sales-report',
            with: [
                'stats' => $this->stats,
                'date' => $this->date,
                'reportPath' => $this->reportPath,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            \Storage::disk('local')->path($this->reportPath),
        ];
    }
}
