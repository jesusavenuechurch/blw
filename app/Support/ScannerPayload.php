<?php

namespace App\Support;

use App\Models\Partner;

/** The partner shape the scanner app stores in its local SQLite database. */
class ScannerPayload
{
    public static function partner(Partner $p): array
    {
        return [
            'id' => $p->id,
            'title' => $p->title,
            'full_name' => $p->full_name,
            'tier' => $p->tier,
            'tier_label' => $p->tier_display,
            'church' => $p->church,
            'zone' => $p->zone,
            'is_registered' => (bool) $p->is_registered,
            'coming_with_spouse' => (bool) $p->coming_with_spouse,
            'spouse_title' => $p->spouse_title,
            'spouse_name' => $p->spouse_name,
            'qr_token' => $p->verification_token,
            'photo_version' => $p->photo_version,
            'updated_at' => $p->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Reads a scanned QR code: a verify link (…/partner/verify/{id}?t=…),
     * or a bare partner ID typed in by hand.
     *
     * @return array{id: int|null, token: string|null}
     */
    public static function parseCode(string $raw): array
    {
        $raw = trim($raw);

        if (ctype_digit($raw)) {
            return ['id' => (int) $raw, 'token' => null];
        }

        if (preg_match('~/partner/verify/(\d+)~', $raw, $m)) {
            parse_str((string) parse_url($raw, PHP_URL_QUERY), $query);

            return ['id' => (int) $m[1], 'token' => isset($query['t']) ? (string) $query['t'] : null];
        }

        return ['id' => null, 'token' => null];
    }
}
