<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartnerRequest;
use App\Jobs\SendPartnerEmail;
use App\Models\Partner;
use App\Support\Options;
use App\Support\PartnerData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PeopleController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('workspace/people', [
            'partners' => fn () => Partner::withCount('checkIns')
                ->orderByDesc('updated_at')
                ->get()
                ->map(fn ($p) => PartnerData::card($p))
                ->values(),
            'selected' => function () use ($request) {
                $id = $request->integer('partner');
                $partner = $id ? Partner::withCount('checkIns')->find($id) : null;

                return $partner ? PartnerData::detail($partner) : null;
            },
            'options' => [
                'titles' => Options::TITLES,
                'designations' => Options::DESIGNATIONS,
                'tiers' => Partner::TIERS,
            ],
        ]);
    }

    public function store(PartnerRequest $request)
    {
        $partner = Partner::create($request->partnerData());

        // Same as the old Filament CreatePartner::afterCreate hook.
        SendPartnerEmail::dispatch($partner);

        return redirect()
            ->route('workspace.people', ['partner' => $partner->id])
            ->with('success', "{$partner->full_name} was added and their invitation is on its way.");
    }

    public function update(PartnerRequest $request, Partner $partner)
    {
        $partner->update($request->partnerData());

        return back()->with('success', 'Changes saved.');
    }

    public function destroy(Partner $partner)
    {
        $name = $partner->full_name;
        $partner->delete();

        return redirect()->route('workspace.people')->with('success', "{$name} was removed.");
    }

    public function resend(Partner $partner)
    {
        $this->queueEmail($partner);

        return back()->with('success', "Email to {$partner->full_name} has been queued.");
    }

    public function regenerate(Partner $partner)
    {
        if ($partner->is_registered) {
            return back()->with('error', 'This partner has already registered.');
        }

        $partner->regenerateRegistrationToken();
        $this->queueEmail($partner);

        return back()->with('success', "A new registration link was generated and sent to {$partner->email}.");
    }

    public function qr(Partner $partner)
    {
        return $partner->generateQrCode()
            ? back()->with('success', 'QR code generated.')
            : back()->with('error', 'Could not generate the QR code. Check the logs for details.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['qr', 'resend', 'delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $partners = Partner::whereIn('id', $data['ids'])->get();

        switch ($data['action']) {
            case 'qr':
                $ok = 0;
                foreach ($partners as $partner) {
                    try {
                        $partner->generateQrCode() ? $ok++ : null;
                    } catch (\Throwable $e) {
                        Log::error("Exception generating QR code for partner {$partner->id}: ".$e->getMessage());
                    }
                }
                $failed = $partners->count() - $ok;

                return $ok > 0
                    ? back()->with('success', "Generated {$ok} QR code(s).".($failed ? " {$failed} failed." : ''))
                    : back()->with('error', "Failed to generate QR codes for all {$failed} selected partners.");

            case 'resend':
                $partners->each(fn ($p) => $this->queueEmail($p));

                return back()->with('success', "{$partners->count()} email(s) queued.");

            case 'delete':
                $count = $partners->count();
                Partner::whereIn('id', $partners->pluck('id'))->delete();

                return back()->with('success', "{$count} partner(s) removed.");
        }

        return back();
    }

    private function queueEmail(Partner $partner): void
    {
        $partner->update([
            'email_pending' => true,
            'email_sent' => false,
            'email_failed' => false,
            'email_response' => null,
        ]);

        SendPartnerEmail::dispatch($partner);
    }
}
