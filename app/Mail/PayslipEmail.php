<?php

namespace App\Mail;

use App\Models\Payslip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $payslip;

    /**
     * Create a new message instance.
     */
    public function __construct(Payslip $payslip)
    {
        $this->payslip = $payslip;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payslip: ' . $this->payslip->payroll->batch_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payslip',
            with: [
                'payslip' => $this->payslip,
                'employee' => $this->payslip->employee
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $this->payslip->load(['employee.user', 'employee.department', 'payroll']);
        
        $pdf = Pdf::loadView('pdf.payslip', [
            'payslip' => $this->payslip,
            'payroll' => $this->payslip->payroll
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'Payslip_' . $this->payslip->payslip_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
