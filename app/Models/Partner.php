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

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'designation',
        'full_name',
        'email',
        'phone',
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
    ];

    protected $casts = [
        'will_attend_ippc' => 'boolean',
        'will_be_at_exhibition' => 'boolean',
        'is_registered' => 'boolean',
        'token_used_at' => 'datetime',
    ];

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

        // Generate QR code with color ON CREATION
        static::created(function ($partner) {
            if (!$partner->id) {
                Log::error('Partner created without ID');
                return;
            }

            $partner->generateColoredQrCode();
        });

        // Regenerate QR code when tier changes
        static::updated(function ($partner) {
            if ($partner->isDirty('tier')) {
                Log::info("Tier changed for Partner ID: {$partner->id}, regenerating QR code");
                $partner->generateColoredQrCode();
            }
        });
    }

    /**
     * Generate QR code with tier-specific color (using the reliable file method)
     */
    public function generateColoredQrCode(): bool
    {
        try {
            // Ensure the partner has an ID
            if (!$this->id) {
                Log::error('Cannot generate QR code: Partner ID is missing');
                return false;
            }

            // Create directory if it doesn't exist
            $directory = storage_path('app/public/qr_codes');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $filename = 'qr_codes/partner_' . $this->id . '.png';
            $path = storage_path('app/public/' . $filename);

            // Generate verification URL
            $verificationUrl = route('partner.verify', ['id' => $this->id]);
            
            if (!$verificationUrl) {
                Log::warning("Route partner.verify not found for Partner ID: {$this->id}");
                return false;
            }

            // Get tier color
            $color = $this->tier_color;
            
            Log::info("Generating QR code for Partner ID: {$this->id} with tier: {$this->tier}", [
                'color' => $color,
                'url' => $verificationUrl
            ]);

            // Generate QR code with color using the file path method
            QrCode::format('png')
                ->size(300)
                ->color($color['r'], $color['g'], $color['b'])
                ->generate($verificationUrl, $path);

            // Verify the file was created and has content
            if (!file_exists($path)) {
                Log::error("QR code file was not created at path: {$path}");
                return false;
            }

            $fileSize = filesize($path);
            if ($fileSize === 0 || $fileSize === false) {
                Log::error("QR code file is empty at path: {$path}");
                return false;
            }

            // Update without triggering events
            $this->updateQuietly(['qr_code_path' => $filename]);

            Log::info("QR code successfully generated for Partner ID: {$this->id} with tier {$this->tier}, file size: {$fileSize} bytes");
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to generate QR code for partner ' . $this->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'tier' => $this->tier,
                'color' => $color ?? 'not set'
            ]);
            return false;
        }
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
        $this->update([
            'token_used_at' => now(),
            'is_registered' => true,
        ]);
        
        Log::info("Partner ID {$this->id} marked as registered");

        // Regenerate QR code with color AFTER registration is complete
        // This ensures the QR code has the correct tier color
        $this->generateColoredQrCode();
        Log::info("QR code regenerated for registered Partner ID: {$this->id}");

        try {
            Mail::to($this->email)->send(new PartnerConfirmationMail($this));
            Log::info("Confirmation email sent to Partner ID: {$this->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send confirmation email to Partner ID {$this->id}: " . $e->getMessage());
            // Don't throw - registration should still succeed even if email fails
        }
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

    /**
     * Force regenerate QR code (for manual fixes)
     */
    public function forceRegenerateQrCode(): bool
    {
        Log::info("Force regenerating QR code for Partner ID: {$this->id}");
        return $this->generateColoredQrCode();
    }
}