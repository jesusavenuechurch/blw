<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Services\PartnerPhotoService;

/**
 * Serves private partner photos. Reached through three guarded routes:
 * a signed link (the partner's own success page), the staff workspace,
 * and the scanner API.
 */
class PartnerPhotoController extends Controller
{
    public function __invoke(Partner $partner, PartnerPhotoService $photos)
    {
        abort_unless($photos->exists($partner), 404);

        return response()->file($photos->absolutePath($partner), [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, max-age=31536000, immutable',
        ]);
    }
}
