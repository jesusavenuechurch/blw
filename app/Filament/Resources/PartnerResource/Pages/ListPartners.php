<?php

namespace App\Filament\Resources\PartnerResource\Pages;

use App\Filament\Resources\PartnerResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;

class ListPartners extends ListRecords
{
    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Register Partner')
                ->url(PartnerResource::getUrl('create')),

            Action::make('batch_upload')
                ->label('Batch Upload')
                ->icon('heroicon-o-arrow-up')
                 ->url(PartnerResource::getUrl('batch-upload')),
        ];
    }
}