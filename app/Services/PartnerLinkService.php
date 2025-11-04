<?php

namespace App\Services;

use App\Models\Partner;
use App\Jobs\SendPartnerRegistrationLink;
use Illuminate\Support\Str;

class PartnerLinkService
{
    /**
     * Create a partner invitation (just email) and send registration link
     */
    public function createInvitation(string $email, ?string $fullName = null): Partner
    {
        // Create minimal partner record with only email
        $partner = Partner::create([
            'email' => $email,
            'full_name' => $fullName ?? 'Guest',
            'registration_token' => Str::random(64),
            'registration_completed' => false,
            'email_pending' => true,
        ]);

        // Send registration link email
        dispatch(new SendPartnerRegistrationLink($partner->id));

        return $partner;
    }

    /**
     * Generate registration link for a partner
     */
    public function getRegistrationLink(Partner $partner): string
    {
        if (!$partner->registration_token) {
            $partner->registration_token = Str::random(64);
            $partner->save();
        }

        return url("/partner/register/{$partner->registration_token}");
    }

    /**
     * Resend registration link
     */
    public function resendLink(Partner $partner): bool
    {
        if ($partner->registration_completed) {
            return false; // Already registered
        }

        dispatch(new SendPartnerRegistrationLink($partner->id));
        return true;
    }

    /**
     * Check if partner needs to complete registration
     */
    public function needsRegistration(Partner $partner): bool
    {
        return !$partner->registration_completed && $partner->registration_token !== null;
    }
}