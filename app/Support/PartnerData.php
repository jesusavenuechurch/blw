<?php

namespace App\Support;

use App\Models\Partner;
use Illuminate\Support\Facades\Storage;

/**
 * Shapes Partner models for the React workspace.
 */
class PartnerData
{
    /** Lightweight shape used by boards, galleries and lists. */
    public static function card(Partner $p): array
    {
        return [
            'id' => $p->id,
            'title' => $p->title,
            'full_name' => $p->full_name,
            'email' => $p->email,
            'phone' => $p->phone,
            'kc_handle' => $p->kc_handle,
            'designation' => $p->designation,
            'region' => $p->region,
            'zone' => $p->zone,
            'church' => $p->church,
            'tier' => $p->tier,
            'stage' => $p->stage,
            'email_state' => $p->email_state,
            'is_registered' => (bool) $p->is_registered,
            'will_attend_ippc' => (bool) $p->will_attend_ippc,
            'will_be_at_exhibition' => (bool) $p->will_be_at_exhibition,
            'coming_with_spouse' => (bool) $p->coming_with_spouse,
            'spouse_name' => $p->spouse_name,
            'ror_copies_sponsored' => (int) $p->ror_copies_sponsored,
            'has_qr' => filled($p->qr_code_path),
            'photo_url' => $p->photo_path ? route('workspace.people.photo', ['partner' => $p->id, 'v' => $p->photo_version]) : null,
            'created_at' => $p->created_at?->toIso8601String(),
            'updated_at' => $p->updated_at?->toIso8601String(),
        ];
    }

    /** Full profile used by the partner panel. */
    public static function detail(Partner $p): array
    {
        $p->loadMissing(['checkIns' => fn ($q) => $q->latest('checked_in_at')]);

        $timeline = collect([
            ['type' => 'created', 'label' => 'Added to the guest list', 'at' => $p->created_at],
        ]);

        if ($p->email_sent) {
            $timeline->push(['type' => 'email', 'label' => 'Invitation email delivered', 'at' => null]);
        }
        if ($p->email_failed) {
            $timeline->push(['type' => 'email_failed', 'label' => 'Invitation email failed', 'at' => null]);
        }
        if ($p->token_used_at) {
            $timeline->push(['type' => 'registered', 'label' => 'Completed registration', 'at' => $p->token_used_at]);
        }
        foreach ($p->checkIns as $checkIn) {
            $timeline->push(['type' => 'checked_in', 'label' => 'Checked in at the lounge', 'at' => $checkIn->checked_in_at]);
        }

        return [
            ...self::card($p),
            'spouse_title' => $p->spouse_title,
            'spouse_kc_handle' => $p->spouse_kc_handle,
            'group' => $p->group,
            'delivery_method' => $p->delivery_method,
            'email_response' => $p->email_response,
            'registration_url' => $p->registration_url,
            'photo_consent_at' => $p->photo_consent_at?->toIso8601String(),
            'qr_code_url' => $p->qr_code_path ? Storage::disk('public')->url($p->qr_code_path) : null,
            'token_used_at' => $p->token_used_at?->toIso8601String(),
            'check_ins' => $p->checkIns->map(fn ($c) => [
                'id' => $c->id,
                'checked_in_at' => $c->checked_in_at?->toIso8601String(),
            ])->values(),
            'timeline' => $timeline
                ->map(fn ($e) => [...$e, 'at' => $e['at']?->toIso8601String()])
                ->sortBy(fn ($e) => $e['at'] ?? '')
                ->values(),
        ];
    }
}
