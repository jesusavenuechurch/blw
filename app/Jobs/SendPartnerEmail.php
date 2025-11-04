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

class SendPartnerEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = [60, 300, 600];

    public int $partnerId;

    public function __construct(Partner $partner)
    {
        $this->partnerId = $partner->id;
        Log::info("SendPartnerEmail job constructed for Partner ID: {$this->partnerId}");
    }

    public function handle(): void
    {
        Log::info("=== SendPartnerEmail handle() method called for Partner ID: {$this->partnerId} ===");
        
        // Load the partner fresh from database
        $partner = Partner::find($this->partnerId);
        
        if (!$partner) {
            Log::error("Partner ID {$this->partnerId} not found in database");
            return;
        }
        
        Log::info("Partner found: {$partner->full_name}, Email: {$partner->email}, email_sent: " . ($partner->email_sent ? 'true' : 'false'));
        
        // Check if email was already sent
        if ($partner->email_sent) {
            Log::info("Email already sent to Partner ID: {$partner->id} - Skipping");
            return;
        }

        Log::info("Starting email send for Partner ID: {$partner->id}, Attempt: {$this->attempts()}");
        
        try {
            Log::info("About to send email to: {$partner->email}");
            
            Mail::to($partner->email)->send(new \App\Mail\PartnerRegisteredMail($partner));
            
            Log::info("Mail::send() completed successfully for: {$partner->email}");
            
            $partner->updateQuietly([
                'email_pending' => false,
                'email_sent' => true,
                'email_failed' => false,
                'email_response' => 'Sent successfully on attempt ' . $this->attempts()
            ]);
            
            Log::info("Partner status updated successfully");
            
        } catch (\Exception $e) {
            Log::error("Email failed for Partner ID {$partner->id} (Attempt {$this->attempts()}): " . $e->getMessage());
            Log::error("Exception type: " . get_class($e));
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            if ($this->attempts() >= $this->tries) {
                Log::error("Final attempt failed. Marking as permanently failed.");
            } else {
                Log::info("Will retry. Next attempt in " . ($this->backoff[$this->attempts() - 1] ?? 60) . " seconds");
            }
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("=== SendPartnerEmail failed() method called ===");
        Log::error("SendPartnerEmail job permanently failed for Partner ID: {$this->partnerId} after {$this->tries} attempts");
        Log::error("Exception: " . $exception->getMessage());
        
        $partner = Partner::find($this->partnerId);
        
        if ($partner && !$partner->email_sent) {
            $partner->updateQuietly([
                'email_pending' => false,
                'email_failed' => true,
                'email_response' => 'Failed after ' . $this->tries . ' attempts: ' . substr($exception->getMessage(), 0, 200)
            ]);
            
            Log::info("Partner marked as email failed");
        }
    }

    public function backoff(): array
    {
        return $this->backoff;
    }
}