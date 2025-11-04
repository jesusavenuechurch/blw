<?php

namespace App\Filament\Resources\PartnerResource\Pages;

use App\Filament\Resources\PartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Jobs\SendPartnerEmail;

class CreatePartner extends CreateRecord
{
    protected static string $resource = PartnerResource::class;

    protected function afterCreate(): void
    {
        // Dispatch email job after record is created
        SendPartnerEmail::dispatch($this->record);
    }
}
