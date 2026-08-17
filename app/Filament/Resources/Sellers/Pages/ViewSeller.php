<?php

namespace App\Filament\Resources\Sellers\Pages;

use App\Filament\Resources\Sellers\SellerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSeller extends ViewRecord
{
    protected static string $resource = SellerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
