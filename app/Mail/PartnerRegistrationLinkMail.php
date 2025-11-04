<?php

namespace App\Mail;

use App\Models\Partner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PartnerRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public Partner $partner;
    public string $registrationUrl;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
        // ✅ FIXED: Generate the URL directly here
        $this->registrationUrl = route('partner.register', ['token' => $partner->registration_token]);
    }

    public function build()
    {
        $mail = $this->markdown('emails.partner.registered')
            ->subject('Complete Your IPPC 2025 Partner Registration')
            ->with([
                'partner' => $this->partner,
                'registrationUrl' => $this->registrationUrl,
            ]);

        // Attach QR code if exists
        if ($this->partner->qr_code_path && Storage::disk('public')->exists($this->partner->qr_code_path)) {
            $mail->attach(storage_path('app/public/' . $this->partner->qr_code_path), [
                'as' => 'QR_Code_' . $this->partner->full_name . '.png',
                'mime' => 'image/png',
            ]);
        }

        return $mail;
    }
}