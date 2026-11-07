<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Partner;
use Carbon\Carbon;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('workspace/home', [
            'pulse' => fn () => $this->pulse(),
            'activity' => fn () => $this->activity(),
            'week' => fn () => self::lastSevenDays(),
        ]);
    }

    private function pulse(): array
    {
        $total = Partner::count();
        $registered = Partner::where('is_registered', true)->count();
        $delivered = Partner::where('is_registered', false)->where('email_sent', true)->count();
        $checkedIn = CheckIn::distinct('partner_id')->count('partner_id');

        $tiers = Partner::selectRaw('tier, count(*) as total, sum(case when is_registered = 1 then 1 else 0 end) as registered')
            ->groupBy('tier')
            ->get()
            ->keyBy('tier');

        return [
            'total' => $total,
            'funnel' => [
                'invited' => $total,
                'delivered' => $delivered + $registered,
                'registered' => $registered,
                'checked_in' => $checkedIn,
            ],
            'awaiting' => $total - $registered,
            'email' => [
                'failed' => Partner::where('email_failed', true)->count(),
                'pending' => Partner::where('email_pending', true)->count(),
                'sent' => Partner::where('email_sent', true)->count(),
            ],
            'attending_ippc' => Partner::where('will_attend_ippc', true)->count(),
            'lounge_guests' => Partner::where('will_be_at_exhibition', true)->count(),
            'with_spouse' => Partner::where('coming_with_spouse', true)->count(),
            'ror_copies' => (int) Partner::sum('ror_copies_sponsored'),
            'today_check_ins' => CheckIn::whereDate('checked_in_at', today())->count(),
            'tiers' => collect(Partner::TIERS)->map(fn ($t) => [
                'tier' => $t,
                'total' => (int) ($tiers[$t]->total ?? 0),
                'registered' => (int) ($tiers[$t]->registered ?? 0),
            ])->values(),
        ];
    }

    private function activity(): array
    {
        $registrations = Partner::whereNotNull('token_used_at')
            ->latest('token_used_at')
            ->limit(12)
            ->get(['id', 'title', 'full_name', 'tier', 'token_used_at'])
            ->map(fn ($p) => [
                'key' => "r{$p->id}",
                'type' => 'registered',
                'partner' => ['id' => $p->id, 'title' => $p->title, 'full_name' => $p->full_name, 'tier' => $p->tier],
                'at' => $p->token_used_at->toIso8601String(),
            ]);

        $checkIns = CheckIn::with('partner:id,title,full_name,tier')
            ->latest('checked_in_at')
            ->limit(12)
            ->get()
            ->filter(fn ($c) => $c->partner)
            ->map(fn ($c) => [
                'key' => "c{$c->id}",
                'type' => 'checked_in',
                'partner' => [
                    'id' => $c->partner->id,
                    'title' => $c->partner->title,
                    'full_name' => $c->partner->full_name,
                    'tier' => $c->partner->tier,
                ],
                'at' => $c->checked_in_at->toIso8601String(),
            ]);

        return $registrations->concat($checkIns)->sortByDesc('at')->take(14)->values()->all();
    }

    public static function lastSevenDays(): array
    {
        return collect(range(6, 0))->map(function ($i) {
            $day = Carbon::today()->subDays($i);

            return [
                'date' => $day->toDateString(),
                'label' => $day->format('D'),
                'count' => CheckIn::whereDate('checked_in_at', $day)->count(),
            ];
        })->all();
    }
}
