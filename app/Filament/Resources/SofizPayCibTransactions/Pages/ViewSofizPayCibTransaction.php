<?php

namespace App\Filament\Resources\SofizPayCibTransactions\Pages;

use App\Filament\Resources\SofizPayCibTransactions\SofizPayCibTransactionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSofizPayCibTransaction extends ViewRecord
{
    protected static string $resource = SofizPayCibTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
