<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\ScannerDevice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScannersController extends Controller
{
    public function index(Request $request)
    {
        $pairingId = $request->session()->get('pairing_device_id');

        return Inertia::render('workspace/scanners', [
            'devices' => fn () => ScannerDevice::withCount([
                'checkIns',
                'checkIns as today_count' => fn ($q) => $q->whereDate('checked_in_at', today()),
            ])
                ->latest()
                ->get()
                ->map(fn ($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'status' => $d->status,
                    'platform' => $d->platform,
                    'app_version' => $d->app_version,
                    'paired_at' => $d->paired_at?->toIso8601String(),
                    'last_seen_at' => $d->last_seen_at?->toIso8601String(),
                    'check_ins' => $d->check_ins_count,
                    'today' => $d->today_count,
                ]),
            'pairing' => function () use ($pairingId) {
                $d = $pairingId ? ScannerDevice::find($pairingId) : null;

                return $d && $d->status === 'waiting' ? $this->pairingPayload($d) : null;
            },
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:60']]);
        $device = ScannerDevice::invite($data['name'], $request->user()->id);

        return back()->with('pairing_device_id', $device->id);
    }

    public function renew(ScannerDevice $device)
    {
        abort_if($device->paired_at || $device->revoked_at, 422);

        $device->update(['pairing_code' => ScannerDevice::newCode(), 'pairing_expires_at' => now()->addMinutes(ScannerDevice::PAIRING_MINUTES)]);

        return back()->with('pairing_device_id', $device->id);
    }

    public function destroy(ScannerDevice $device)
    {
        if ($device->paired_at) {
            $device->revoke();

            return back()->with('success', "{$device->name} can no longer scan.");
        }

        $device->delete();

        return back()->with('success', 'Pairing cancelled.');
    }

    private function pairingPayload(ScannerDevice $d): array
    {
        return [
            'id' => $d->id,
            'name' => $d->name,
            'code' => $d->pairing_code,
            'expires_at' => $d->pairing_expires_at->toIso8601String(),
            // What the app reads from the pairing QR: where the server is, and the code.
            'qr' => json_encode(['type' => 'blw-scanner-pair', 'api' => url('/api'), 'code' => $d->pairing_code], JSON_UNESCAPED_SLASHES),
            'api' => url('/api'),
        ];
    }
}
