<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /** JSON search used by the ⌘K command palette. */
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q'));

        if ($q === '') {
            return response()->json([]);
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';

        return response()->json(
            Partner::where(fn ($w) => $w->where('full_name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('church', 'like', $like))
                ->orderBy('full_name')
                ->limit(8)
                ->get(['id', 'title', 'full_name', 'email', 'tier', 'is_registered'])
        );
    }
}
