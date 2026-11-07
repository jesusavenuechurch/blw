<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Partner;
use App\Services\CheckinReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckinsController extends Controller
{
    /** First day of the event, as used by the old dashboard widgets. */
    private const EVENT_START = '2025-11-08';

    public function index()
    {
        $start = Carbon::parse(self::EVENT_START)->startOfDay();

        return Inertia::render('workspace/checkins', [
            'stats' => fn () => [
                'total' => CheckIn::where('checked_in_at', '>=', $start)->count(),
                'today' => CheckIn::whereDate('checked_in_at', today())->count(),
                'unique_today' => CheckIn::whereDate('checked_in_at', today())->distinct('partner_id')->count('partner_id'),
                'with_spouse' => CheckIn::where('checked_in_at', '>=', $start)
                    ->whereHas('partner', fn ($q) => $q->where('coming_with_spouse', 1))
                    ->count(),
                'expected' => Partner::where('is_registered', true)->count(),
                'arrived' => CheckIn::distinct('partner_id')->count('partner_id'),
            ],
            'week' => fn () => HomeController::lastSevenDays(),
            'feed' => fn () => CheckIn::with('partner')
                ->where('checked_in_at', '>=', $start)
                ->latest('checked_in_at')
                ->limit(200)
                ->get()
                ->filter(fn ($c) => $c->partner)
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'checked_in_at' => $c->checked_in_at->toIso8601String(),
                    'partner' => [
                        'id' => $c->partner->id,
                        'title' => $c->partner->title,
                        'full_name' => $c->partner->full_name,
                        'tier' => $c->partner->tier,
                        'coming_with_spouse' => (bool) $c->partner->coming_with_spouse,
                        'spouse_name' => $c->partner->spouse_name,
                    ],
                ])
                ->values(),
            'range' => [
                'start' => self::EVENT_START,
                'end' => today()->toDateString(),
            ],
        ]);
    }

    public function report(Request $request)
    {
        $data = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
        ]);

        $report = (new CheckinReportService)->generateExcel(Carbon::parse($data['start']), Carbon::parse($data['end']));

        return response()->streamDownload(
            fn () => print ($report['content']),
            $report['filename'],
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }
}
