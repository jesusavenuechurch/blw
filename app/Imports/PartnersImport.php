<?php

namespace App\Imports;

use App\Models\Partner;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class PartnersImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Check if partner with this email already exists
            $existingPartner = Partner::where('email', $row['email'])->first();
            
            if ($existingPartner) {
                Log::info("Partner already exists: {$row['email']} - Skipping");
                continue; // Skip this row
            }
           //dd($row);
            $partner = Partner::create([
                'title' => $row['title'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'phone' => $row['phone'] ?? null,
                'region' => $row['region'] ?? null,
                'zone' => $row['zone'] ?? null,
                'group' => $row['group'] ?? null,
                'church' => $row['church'] ?? null,
                'tier' => strtoupper(trim($row['tier'] ?? '')), // handle DIAMOND properly
                'ror_copies_sponsored' => (int) str_replace(',', '', $row['copies_sponsored'] ?? 0),
            ]);

            Log::info("Partner created via import: ID={$partner->id}, tier={$partner->tier}");
            
            // Refresh to ensure QR code path is loaded
            $partner->refresh();
            
            Log::info("QR Code Path after refresh: {$partner->qr_code_path}");

            // Dispatch email job
            dispatch(new \App\Jobs\SendPartnerEmail($partner));
            
            Log::info("Email job dispatched for Partner ID: {$partner->id}");
        }
    }
}