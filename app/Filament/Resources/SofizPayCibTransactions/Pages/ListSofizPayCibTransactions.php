<?php

namespace App\Filament\Resources\SofizPayCibTransactions\Pages;

use App\Filament\Resources\SofizPayCibTransactions\SofizPayCibTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSofizPayCibTransactions extends ListRecords
{
    protected static string $resource = SofizPayCibTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
