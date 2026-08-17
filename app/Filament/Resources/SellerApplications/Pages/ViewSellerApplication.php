<?php

namespace App\Filament\Resources\SellerApplications\Pages;

use App\Filament\Resources\SellerApplications\SellerApplicationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSellerApplication extends ViewRecord
{
    protected static string $resource = SellerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
