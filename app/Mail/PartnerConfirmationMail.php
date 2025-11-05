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
        // Generate subject dynamically based on partner's tier
        $subject = 'Confirmation of Registration – ' . ucfirst($this->partner->tier_display) . ' Lounge';

        $mail = $this->markdown('emails.partner.confirmation')
            ->subject($subject)
            ->with([
                'partner' => $this->partner,
            ]);

        // Attach the partner's QR code if it exists
        if ($this->partner->qr_code_path && Storage::disk('public')->exists($this->partner->qr_code_path)) {
            $mail->attach(storage_path('app/public/' . $this->partner->qr_code_path), [
                'as' => 'Angel_Lounge_QR_' . str_replace(' ', '_', $this->partner->full_name) . '.png',
                'mime' => 'image/png',
            ]);
        }

        return $mail;
    }
}
