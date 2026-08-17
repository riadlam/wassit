<?php

namespace App\Filament\Resources\SuperDiscountOffers\Pages;

use App\Filament\Resources\SuperDiscountOffers\SuperDiscountOfferResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSuperDiscountOffer extends EditRecord
{
    protected static string $resource = SuperDiscountOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
