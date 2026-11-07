<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Mail\PartnerConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'designation',
        'full_name',
        'email',
        'phone',
        'kc_handle',
        'region',
        'zone',
        'group',
        'church',
        'tier',
        'ror_copies_sponsored',
        'will_attend_ippc',
        'will_be_at_exhibition',
        'delivery_method',
        'qr_code_path',
        'email_pending',
        'email_sent',
        'email_failed',
        'verification_token',
        'email_response',
        'registration_token',
        'token_used_at',
        'is_registered',
        'coming_with_spouse',
        'spouse_title',
        'spouse_name',  
        'spouse_kc_handle',
        'photo_path',
        'photo_updated_at',
        'photo_consent_at',
    ];

    protected $casts = [
        'will_attend_ippc' => 'boolean',
        'will_be_at_exhibition' => 'boolean',
        'is_registered' => 'boolean',
        'email_pending' => 'boolean',
        'email_sent' => 'boolean',
        'email_failed' => 'boolean',
        'token_used_at' => 'datetime',
        'photo_updated_at' => 'datetime',
        'photo_consent_at' => 'datetime',
    ];

    /** Tier keys in display order. */
    public const TIERS = ['ruby', 'silver', 'gold', 'diamond', 'as_one_man', 'top_individual'];

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    /**
     * Email delivery state derived from the email_* flags.
     */
    public function getEmailStateAttribute(): string
    {
        return match (true) {
            (bool) $this->email_failed => 'failed',
            (bool) $this->email_sent => 'sent',
            (bool) $this->email_pending => 'pending',
            default => 'none',
        };
    }

    /**
     * Where the partner is in the journey: invited → delivered → registered → checked_in.
     * Expects check_ins_count to be loaded (withCount('checkIns')) when available.
     */
    public function getStageAttribute(): string
    {
        $checkIns = $this->check_ins_count ?? ($this->relationLoaded('checkIns') ? $this->checkIns->count() : $this->checkIns()->count());

        return match (true) {
            $checkIns > 0 => 'checked_in',
            (bool) $this->is_registered => 'registered',
            (bool) $this->email_sent => 'delivered',
            default => 'invited',
        };
    }

public function setTierAttribute($value)
{
    // Normalize: lowercase, trim, and remove any emojis or symbols
    $normalized = strtolower(trim($value));
    $normalized = preg_replace('/[^\p{L}\s_]+/u', '', $normalized);

    // If multiple tiers are written (e.g., "diamond gold"), take the first word
    $firstWord = explode(' ', $normalized)[0] ?? $normalized;

    $map = [
        'ruby' => 'ruby',
        'silver' => 'silver',
        'gold' => 'gold',
        'diamond' => 'diamond',
        'as' => 'as_one_man', // handle "as one man"
        'as_one_man' => 'as_one_man',
        'as one man' => 'as_one_man',
        'top' => 'top_individual', // handle "top individual partner"
        'top_individual_partner' => 'top_individual',
    ];

    // Fallback: if firstWord isn't a known tier, store it as-is
    $this->attributes['tier'] = $map[$firstWord] ?? $firstWord;
}


    /**
     * Get the color for the partner's tier
     */
    public function getTierColorAttribute(): array
    {
        return match($this->tier) {
            'ruby' => ['r' => 155, 'g' => 17, 'b' => 30],          // Deep Ruby Red: #9B111E
            'silver' => ['r' => 192, 'g' => 192, 'b' => 192],      // Classic Silver: #C0C0C0
            'gold' => ['r' => 255, 'g' => 215, 'b' => 0],          // Bright Gold: #FFD700
            'diamond' => ['r' => 128, 'g' => 0, 'b' => 128],       // Purple: #800080
            'as_one_man' => ['r' => 200, 'g' => 162, 'b' => 200], // Lilac/Lavender: #C8A2C8
            'top_individual' => ['r' => 64, 'g' => 224, 'b' => 208],      // Turquoise: #40E0D0
            default => ['r' => 0, 'g' => 0, 'b' => 0],             // Black (fallback)
        };
    }

    /**
     * Get the tier display name with emoji
     */
    public function getTierDisplayAttribute(): string
    {
        return match($this->tier) {
            'ruby' => '💎 Ruby',
            'silver' => '🥈 Silver',
            'gold' => '🥇 Gold',
            'diamond' => '💠 Diamond',
            'as_one_man' => '🎵 As One Man',
            'top_individual' => '⭐ Top Individual Partner',
            default => 'No Tier',
        };
    }

    /**
     * Get the registration URL
     */
    public function getRegistrationUrlAttribute(): ?string
    {
        if (empty($this->registration_token)) {
            return null;
        }

        return route('partner.register', ['token' => $this->registration_token]);
    }

    protected static function booted(): void
    {
        static::creating(function ($partner) {
            // Generate tokens automatically on creation
            if (!$partner->registration_token) {
                $partner->registration_token = Str::random(64);
            }
            if (!$partner->verification_token) {
                $partner->verification_token = Str::random(64);
            }
            
            Log::info("Tokens generated for new partner: {$partner->email}");
        });

        // Scanners drop deleted partners on their next sync.
        static::deleted(function ($partner) {
            PartnerTombstone::create(['partner_id' => $partner->id, 'deleted_at' => now()]);
            if ($partner->photo_path) {
                Storage::disk('local')->delete($partner->photo_path);
            }
        });

        // Regenerate QR code when tier changes
        static::updated(function ($partner) {
            if ($partner->isDirty('tier')) {
                Log::info("Tier changed for Partner ID: {$partner->id}, regenerating QR code");
                $partner->generateQrCode();
            }
        });
    }

    /**
     * Generate QR code with tier-specific color
     */
    public function generateQrCode(): ?string
    {
        try {
            $verificationUrl = $this->verification_url;
            if (!$verificationUrl) {
                Log::warning("Route partner.verify not found for Partner ID: {$this->id}");
                return null;
            }

            $color = $this->tier_color;
            $filename = 'qr_codes/partner_' . $this->id . '.png';

            $qrContent = QrCode::format('png')
                ->size(300)
                ->color($color['r'], $color['g'], $color['b'])
                ->generate($verificationUrl);

            Storage::disk('public')->put($filename, $qrContent);

            $this->qr_code_path = $filename;
            $this->save();

            return $filename;
        } catch (\Exception $e) {
            Log::error('Failed to generate QR code for partner ' . $this->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * The link encoded in the partner's QR code. The token makes the code
     * impossible to guess from the partner's ID alone.
     */
    public function getVerificationUrlAttribute(): string
    {
        return route('partner.verify', array_filter([
            'id' => $this->id,
            't' => $this->verification_token,
        ]));
    }

    /** Changes whenever the photo does, so apps know to download it again. */
    public function getPhotoVersionAttribute(): ?int
    {
        return $this->photo_path ? ($this->photo_updated_at?->getTimestamp() ?? 1) : null;
    }

    /**
     * Generate new registration token
     */
    public function regenerateRegistrationToken(): void
    {
        $this->update([
            'registration_token' => Str::random(64),
            'verification_token' => Str::random(64),
            'token_used_at' => null,
        ]);
        
        Log::info("Registration token regenerated for Partner ID: {$this->id}");
    }

    /**
     * Check if the partner can use a registration/verification token
     */
    public function canUseToken(): bool
    {
        return !$this->is_registered && $this->token_used_at === null;
    }

    /**
     * Mark the partner as registered/verified
     */
    public function markTokenAsUsed(): void
    {   
        Log::info("Partner ID {$this->id} marked as registered");

        // Generate QR code AFTER registration is complete
        $this->generateQrCode();
        Log::info("QR code generated for registered Partner ID: {$this->id}");

        try {
            Mail::to($this->email)->send(new PartnerConfirmationMail($this));
            Log::info("Confirmation email sent to Partner ID: {$this->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send confirmation email to Partner ID {$this->id}: " . $e->getMessage());
            // Don't throw - registration should still succeed even if email fails
        }
        $this->update([
            'token_used_at' => now(),
            'is_registered' => true,
        ]);
    }

    /**
     * Get the public URL for the QR code
     */
    public function getQrCodeUrlAttribute(): ?string
    {
        return $this->qr_code_path 
            ? Storage::url($this->qr_code_path) 
            : null;
    }
}