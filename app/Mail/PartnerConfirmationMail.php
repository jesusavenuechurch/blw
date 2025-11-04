<?php

namespace App\Mail;

use App\Models\Partner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PartnerConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Partner $partner;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function build()
    {
        $mail = $this->markdown('emails.partner.confirmation')
            ->subject('IPPC 2025 Registration Confirmed - Your QR Code Inside')
            ->with([
                'partner' => $this->partner,
            ]);

        // Attach QR code (should exist now after registration)
        if ($this->partner->qr_code_path && Storage::disk('public')->exists($this->partner->qr_code_path)) {
            $mail->attach(storage_path('app/public/' . $this->partner->qr_code_path), [
                'as' => 'IPPC_2025_QR_Code_' . $this->partner->full_name . '.png',
                'mime' => 'image/png',
            ]);
        }

        return $mail;
    }
}