<?php
namespace App\Mail;

use App\Models\PendingRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyPendingRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PendingRegistration $pending, public string $signedUrl) {}

    public function build()
    {
        return $this->subject('Verifikasi Pendaftaran Akun')
            ->markdown('mail.verify-pending', [
                'pending'   => $this->pending,
                'signedUrl' => $this->signedUrl,
            ]);
    }
}
