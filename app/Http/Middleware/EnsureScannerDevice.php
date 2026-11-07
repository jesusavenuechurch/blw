<?php

namespace App\Http\Middleware;

use App\Models\ScannerDevice;
use Closure;
use Illuminate\Http\Request;

/** Only paired, non-revoked scanner devices may use the scanner API. */
class EnsureScannerDevice
{
    public function handle(Request $request, Closure $next)
    {
        $device = $request->user();

        if (! $device instanceof ScannerDevice || $device->revoked_at !== null || ! $device->tokenCan('scanner')) {
            return response()->json(['message' => 'This device is not paired. Pair it again from the workspace.'], 401);
        }

        if (! $device->last_seen_at || $device->last_seen_at->lt(now()->subMinute())) {
            $device->forceFill([
                'last_seen_at' => now(),
                'app_version' => $request->header('X-App-Version', $device->app_version),
            ])->saveQuietly();
        }

        return $next($request);
    }
}
