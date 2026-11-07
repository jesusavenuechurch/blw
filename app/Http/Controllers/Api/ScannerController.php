<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Partner;
use App\Models\PartnerTombstone;
use App\Models\ScannerDevice;
use App\Support\ScannerPayload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * API for the lounge scanner app (v1).
 *
 * The app downloads partners into SQLite, scans offline, and syncs:
 * partners and deletions by timestamp, check-ins both ways by cursor.
 * Every check-in carries a UUID made on the phone, so retries never
 * create duplicates.
 */
class ScannerController extends Controller
{
    public function pair(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'platform' => ['nullable', 'string', 'max:30'],
            'app_version' => ['nullable', 'string', 'max:30'],
        ]);

        $device = ScannerDevice::where('pairing_code', ScannerDevice::normalizeCode($data['code']))
            ->whereNull('paired_at')
            ->whereNull('revoked_at')
            ->where('pairing_expires_at', '>', now())
            ->first();

        if (! $device) {
            return response()->json(['message' => 'That pairing code is wrong or has expired. Create a new one in the workspace.'], 422);
        }

        $token = $device->createToken('scanner', ['scanner'])->plainTextToken;

        $device->update([
            'paired_at' => now(),
            'last_seen_at' => now(),
            'pairing_code' => null,
            'platform' => $data['platform'] ?? null,
            'app_version' => $data['app_version'] ?? null,
        ]);

        return response()->json([
            'token' => $token,
            'device' => ['id' => $device->id, 'name' => $device->name],
            'event' => ['name' => 'Angel Lounges', 'subtitle' => 'IPPC 2025'],
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function me(Request $request)
    {
        $device = $request->user();

        return response()->json([
            'device' => ['id' => $device->id, 'name' => $device->name],
            'event' => ['name' => 'Angel Lounges', 'subtitle' => 'IPPC 2025'],
            'server_time' => now()->toIso8601String(),
            'counts' => [
                'partners' => Partner::count(),
                'registered' => Partner::where('is_registered', true)->count(),
                'checked_in_today' => CheckIn::whereDate('checked_in_at', today())->distinct('partner_id')->count('partner_id'),
            ],
        ]);
    }

    /** Partners changed since ?since= (ISO time), plus IDs deleted since then. Omit since for everything. */
    public function partners(Request $request)
    {
        $request->validate(['since' => ['nullable', 'date']]);

        // Taken before querying so nothing changed mid-request is missed next time.
        $serverTime = now();
        $since = $request->filled('since') ? Carbon::parse($request->input('since')) : null;

        $partners = Partner::query()
            ->when($since, fn ($q) => $q->where('updated_at', '>=', $since))
            ->orderBy('id')
            ->get();

        return response()->json([
            'full' => $since === null,
            'server_time' => $serverTime->toIso8601String(),
            'partners' => $partners->map(fn ($p) => ScannerPayload::partner($p))->values(),
            'deleted_ids' => $since
                ? PartnerTombstone::where('deleted_at', '>=', $since)->pluck('partner_id')->unique()->values()
                : [],
        ]);
    }

    /** Online fallback when a scanned partner isn't in the phone's local list. */
    public function lookup(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:2000']]);
        ['id' => $id, 'token' => $token] = ScannerPayload::parseCode($request->input('code'));

        $partner = $id ? Partner::find($id) : null;

        if (! $partner || ($token !== null && ! hash_equals((string) $partner->verification_token, $token))) {
            return response()->json(['message' => 'No partner matches this code.'], 404);
        }

        return response()->json(['partner' => ScannerPayload::partner($partner)]);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) < 2) {
            return response()->json(['partners' => []]);
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';

        return response()->json([
            'partners' => Partner::where(fn ($w) => $w->where('full_name', 'like', $like)->orWhere('spouse_name', 'like', $like))
                ->orderBy('full_name')
                ->limit(25)
                ->get()
                ->map(fn ($p) => ScannerPayload::partner($p))
                ->values(),
        ]);
    }

    /** Upload check-ins made on the phone. Safe to retry: known UUIDs are accepted again without duplicating. */
    public function pushCheckins(Request $request)
    {
        $data = $request->validate([
            'checkins' => ['required', 'array', 'max:500'],
            'checkins.*.uuid' => ['required', 'uuid'],
            'checkins.*.partner_id' => ['required', 'integer'],
            'checkins.*.checked_in_at' => ['required', 'date'],
        ]);

        $device = $request->user();
        $accepted = [];
        $rejected = [];

        $known = CheckIn::whereIn('uuid', collect($data['checkins'])->pluck('uuid'))->pluck('uuid')->flip();
        $partnerIds = Partner::whereIn('id', collect($data['checkins'])->pluck('partner_id'))->pluck('id')->flip();

        DB::transaction(function () use ($data, $device, $known, $partnerIds, &$accepted, &$rejected) {
            foreach ($data['checkins'] as $row) {
                if (isset($known[$row['uuid']])) {
                    $accepted[] = $row['uuid'];

                    continue;
                }
                if (! isset($partnerIds[$row['partner_id']])) {
                    $rejected[] = ['uuid' => $row['uuid'], 'reason' => 'partner_not_found'];

                    continue;
                }

                $at = Carbon::parse($row['checked_in_at'])->setTimezone(config('app.timezone'));
                if ($at->isAfter(now()->addMinutes(5))) {
                    $at = now(); // A phone with a wrong clock shouldn't record the future.
                }

                CheckIn::create([
                    'uuid' => $row['uuid'],
                    'partner_id' => $row['partner_id'],
                    'scanner_device_id' => $device->id,
                    'checked_in_at' => $at,
                ]);
                $accepted[] = $row['uuid'];
            }
        });

        return response()->json([
            'accepted' => $accepted,
            'rejected' => $rejected,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Check-ins recorded anywhere (other scanners, the old API) after ?after_id=,
     * so every door knows who has already arrived. Pages of 1000.
     */
    public function pullCheckins(Request $request)
    {
        $request->validate(['after_id' => ['nullable', 'integer', 'min:0']]);
        $after = (int) $request->input('after_id', 0);

        $rows = CheckIn::with('device:id,name')
            ->where('id', '>', $after)
            // On a first sync, only recent days matter at the door.
            ->when($after === 0, fn ($q) => $q->where('checked_in_at', '>=', today()->subDays(2)))
            ->orderBy('id')
            ->limit(1000)
            ->get();

        return response()->json([
            'checkins' => $rows->map(fn ($c) => [
                'id' => $c->id,
                'uuid' => $c->uuid,
                'partner_id' => $c->partner_id,
                'checked_in_at' => $c->checked_in_at?->toIso8601String(),
                'device_name' => $c->device?->name,
            ])->values(),
            // With nothing new, a first sync still jumps past old history.
            'last_id' => $rows->last()?->id ?? ($after === 0 ? (int) CheckIn::max('id') : $after),
            'has_more' => $rows->count() === 1000,
        ]);
    }
}
