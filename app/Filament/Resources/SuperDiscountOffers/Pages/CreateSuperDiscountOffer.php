<?php

namespace App\Filament\Resources\SuperDiscountOffers\Pages;

use App\Filament\Resources\SuperDiscountOffers\SuperDiscountOfferResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSuperDiscountOffer extends CreateRecord
{
    protected static string $resource = SuperDiscountOfferResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['image_path'] ?? null)) {
            $data['image_path'] = null;
        }

        return $data;
    }
}
