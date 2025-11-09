<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PartnerApiController extends Controller
{
    /**
     * Download all partners data for offline verification
     * Protected by simple token authentication
     */
    public function downloadPartners(Request $request)
    {
        // Simple authentication - check for access token
        $token = $request->header('X-Access-Token') ?? $request->input('token');
        
        // Replace with your own secret token (store in .env)
        $validToken = env('SCANNER_ACCESS_TOKEN', 'your-secret-token-here');
        
        if ($token !== $validToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }

        try {
            // Get only registered partners with minimal info
            $partners = Partner::where('is_registered', true)
                ->select([
                    'id',
                    'title',
                    'full_name',
                    'tier',
                    'coming_with_spouse',
                    'spouse_name',
                ])
                ->get()
                ->map(function ($partner) {
                    return [
                        'id' => $partner->id,
                        'title' => $partner->title,
                        'full_name' => $partner->full_name,
                        'tier' => $partner->tier,
                        'tier_display' => $partner->tier_display,
                        'coming_with_spouse' => $partner->coming_with_spouse,
                        'spouse_name' => $partner->spouse_name,
                    ];
                });

            return response()->json([
                'success' => true,
                'count' => $partners->count(),
                'downloaded_at' => now()->toIso8601String(),
                'partners' => $partners
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch partners data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify a single partner (online fallback if needed)
     */
    public function verifyPartner($id)
    {
        try {
            $partner = Partner::where('id', $id)
                ->where('is_registered', true)
                ->first();

            if (!$partner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Partner not found or not registered'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'partner' => [
                    'id' => $partner->id,
                    'title' => $partner->title,
                    'full_name' => $partner->full_name,
                    'tier' => $partner->tier,
                    'tier_display' => $partner->tier_display,
                    'coming_with_spouse' => $partner->coming_with_spouse,
                    'spouse_name' => $partner->spouse_name,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sync status - returns count and last update time
     */
    public function syncStatus()
    {
        return response()->json([
            'success' => true,
            'total_partners' => Partner::where('is_registered', true)->count(),
            'last_updated' => Partner::where('is_registered', true)->latest('updated_at')->first()?->updated_at->toIso8601String(),
        ]);
    }
}