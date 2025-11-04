<?php

namespace App\Jobs;

use App\Models\Partner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPartnerRegistrationLink implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 10;
    public $timeout = 120;

    protected int $partnerId;

    public function __construct(int $partnerId)
    {
        $this->partnerId = $partnerId;
    }

    public function handle(): void
    {
        $partner = Partner::find($this->partnerId);
        
        if (!$partner) {
            Log::warning("Partner ID {$this->partnerId} not found - skipping registration link email");
            return;
        }
        
        // Check if already registered
        if ($partner->registration_completed) {
            Log::info("Partner ID {$partner->id} already registered - skipping link email");
            return;
        }

        Log::info("Sending registration link to: {$partner->email}");
        
        try {
            $registrationLink = url("/partner/register/{$partner->registration_token}");
            
            Mail::to($partner->email)->send(new \App\Mail\PartnerRegistrationLinkMail($partner, $registrationLink));
            
            Log::info("Registration link sent successfully to: {$partner->email}");
            
            $partner->email_pending = false;
            $partner->email_sent = true;
            $partner->email_failed = false;
            $partner->save();
            
        } catch (\Exception $e) {
            Log::error("Registration link failed for Partner ID {$partner->id}: " . $e->getMessage());
            
            $partner->email_pending = false;
            $partner->email_failed = true;
            $partner->email_response = substr($e->getMessage(), 0, 255);
            $partner->save();
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendPartnerRegistrationLink job permanently failed for Partner ID: {$this->partnerId}");
        
        $partner = Partner::find($this->partnerId);
        if ($partner) {
            $partner->email_pending = false;
            $partner->email_failed = true;
            $partner->email_response = 'Job failed after ' . $this->tries . ' attempts: ' . substr($exception->getMessage(), 0, 200);
            $partner->save();
        }
    }
}