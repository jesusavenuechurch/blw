<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * A phone running the lounge scanner app. Paired from the workspace with a
 * short-lived code, then authenticated with its own Sanctum token.
 */
class ScannerDevice extends Model implements AuthenticatableContract
{
    use Authenticatable, HasApiTokens;

    /** Devices have no password or "remember me"; they authenticate only with their token. */
    public function getRememberTokenName()
    {
        return '';
    }

    public const PAIRING_MINUTES = 15;

    protected $fillable = ['name', 'pairing_code', 'pairing_expires_at', 'paired_at', 'last_seen_at', 'revoked_at', 'platform', 'app_version', 'created_by'];

    protected $casts = [
        'pairing_expires_at' => 'datetime',
        'paired_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    /** Creates an unpaired device with a fresh pairing code. */
    public static function invite(string $name, ?int $userId = null): self
    {
        return self::create([
            'name' => $name,
            'pairing_code' => self::newCode(),
            'pairing_expires_at' => now()->addMinutes(self::PAIRING_MINUTES),
            'created_by' => $userId,
        ]);
    }

    /** Six characters without look-alikes (no 0/O, 1/I/L). */
    public static function newCode(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (self::where('pairing_code', $code)->exists());

        return $code;
    }

    public static function normalizeCode(string $code): string
    {
        return Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $code));
    }

    public function getStatusAttribute(): string
    {
        return match (true) {
            $this->revoked_at !== null => 'revoked',
            $this->paired_at !== null => 'active',
            $this->pairing_expires_at?->isFuture() => 'waiting',
            default => 'expired',
        };
    }

    public function revoke(): void
    {
        $this->tokens()->delete();
        $this->update(['revoked_at' => now(), 'pairing_code' => null]);
    }
}
