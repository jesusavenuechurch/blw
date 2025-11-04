<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerVerificationController extends Controller
{
    public function verify(Partner $partner)
    {
        // If already registered/verified
        if ($partner->is_registered) {
            return view('partner.verification', [
                'message' => 'Partner already verified.',
                'partner' => $partner,
                'status' => 'already_verified',
            ]);
        }

        // Mark as verified
        $partner->update([
            'is_registered' => true,
            'token_used_at' => now(),
        ]);

        return view('partner.verification', [
            'message' => 'Partner verified successfully!',
            'partner' => $partner,
            'status' => 'verified',
        ]);
    }
}