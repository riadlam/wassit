<?php

namespace App\Filament\Resources\SellerRanks\Pages;

use App\Filament\Resources\SellerRanks\SellerRankResource;
use Filament\Resources\Pages\ListRecords;

class ListSellerRanks extends ListRecords
{
    protected static string $resource = SellerRankResource::class;
}
