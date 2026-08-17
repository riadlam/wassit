<?php

namespace App\Filament\Resources\SuperDiscountOffers\Pages;

use App\Filament\Resources\SuperDiscountOffers\SuperDiscountOfferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuperDiscountOffers extends ListRecords
{
    protected static string $resource = SuperDiscountOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
