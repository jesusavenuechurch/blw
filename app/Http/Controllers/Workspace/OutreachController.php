<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Jobs\SendPartnerEmail;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OutreachController extends Controller
{
    public function index()
    {
        $audience = Partner::selectRaw('tier, count(*) as total')->groupBy('tier')->pluck('total', 'tier');

        return Inertia::render('workspace/outreach', [
            'audience' => collect(Partner::TIERS)->mapWithKeys(fn ($t) => [$t => (int) ($audience[$t] ?? 0)]),
            'health' => [
                'sent' => Partner::where('email_sent', true)->count(),
                'pending' => Partner::where('email_pending', true)->count(),
                'failed' => Partner::where('email_failed', true)->count(),
                'none' => Partner::where('email_sent', false)->where('email_failed', false)->where('email_pending', false)->count(),
            ],
            'failures' => Partner::where('email_failed', true)
                ->latest('updated_at')
                ->limit(50)
                ->get(['id', 'title', 'full_name', 'email', 'tier', 'email_response', 'updated_at'])
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'full_name' => $p->full_name,
                    'email' => $p->email,
                    'tier' => $p->tier,
                    'email_response' => $p->email_response,
                    'updated_at' => $p->updated_at?->toIso8601String(),
                ]),
            'defaults' => [
                'subject' => 'Dinner at Angel Lounges - IPPC 2025',
                'message' => "Dear Esteemed Partners - The Best in the World!\n\nCongratulations on a most impactful IPPC Thursday evening session with our dear Man of God, Pastor Chris Oyakhilome Dsc Dsc DD. What a glorious time of fellowship and impartation!\n\nWe are delighted to inform you that dinner will be served immediately after the evening session at our lounges at Angel Court.\n\nThank you\nAngel Lounges Team",
            ],
        ]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'tiers' => ['required', 'array', 'min:1'],
            'tiers.*' => [Rule::in(Partner::TIERS)],
            'registered_only' => ['boolean'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $partners = Partner::whereIn('tier', $data['tiers'])
            ->when($request->boolean('registered_only'), fn ($q) => $q->where('is_registered', true))
            ->get();

        if ($partners->isEmpty()) {
            return back()->with('error', 'No partners found for the selected tiers.');
        }

        foreach ($partners as $partner) {
            SendPartnerEmail::dispatch($partner, $data['subject'], $data['message']);
        }

        return back()->with('success', "Emails queued for {$partners->count()} partners.");
    }
}
