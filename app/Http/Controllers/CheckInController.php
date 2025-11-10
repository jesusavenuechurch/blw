<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckInController extends Controller
{
    /**
     * Bulk check-in (from mobile app sync)
     */
    public function bulkCheckIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'checkins' => 'required|array',
            'checkins.*.partner_id' => 'required|integer|exists:partners,id',
            'checkins.*.checked_in_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $created = 0;
        $errors = [];

        foreach ($request->checkins as $checkInData) {
            try {
                CheckIn::create([
                    'partner_id' => $checkInData['partner_id'],
                    'checked_in_at' => $checkInData['checked_in_at'],
                ]);
                $created++;
            } catch (\Exception $e) {
                $errors[] = [
                    'partner_id' => $checkInData['partner_id'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'created' => $created,
            'errors' => $errors,
            'message' => "Successfully checked in {$created} partner(s)",
        ]);
    }

    /**
     * Single check-in (optional, for future use)
     */
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'partner_id' => 'required|integer|exists:partners,id',
            'checked_in_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $checkIn = CheckIn::create([
            'partner_id' => $request->partner_id,
            'checked_in_at' => $request->checked_in_at ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $checkIn,
            'message' => 'Check-in successful',
        ], 201);
    }

    /**
     * Get all check-ins (for admin/reports)
     */
    public function index(Request $request)
    {
        $query = CheckIn::with('partner');

        // Optional: Filter by date
        if ($request->has('date')) {
            $query->whereDate('checked_in_at', $request->date);
        }

        // Optional: Filter by partner
        if ($request->has('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        $checkIns = $query->orderBy('checked_in_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'count' => $checkIns->count(),
            'data' => $checkIns,
        ]);
    }

    /**
     * Get check-in statistics
     */
    public function stats()
    {
        $today = now()->startOfDay();
        
        $stats = [
            'total_check_ins' => CheckIn::count(),
            'today_check_ins' => CheckIn::whereDate('checked_in_at', $today)->count(),
            'unique_partners_today' => CheckIn::whereDate('checked_in_at', $today)
                ->distinct('partner_id')
                ->count('partner_id'),
            'last_check_in' => CheckIn::latest('checked_in_at')->first(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Check if partner already checked in today
     */
    public function checkStatus(Request $request, $partnerId)
    {
        $today = now()->startOfDay();
        
        $checkIn = CheckIn::where('partner_id', $partnerId)
            ->whereDate('checked_in_at', $today)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'checked_in' => $checkIn !== null,
            'data' => $checkIn,
        ]);
    }
}