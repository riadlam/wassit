<?php

namespace App\Filament\Resources\SofizPayCibTransactions\Pages;

use App\Filament\Resources\SofizPayCibTransactions\SofizPayCibTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSofizPayCibTransaction extends EditRecord
{
    protected static string $resource = SofizPayCibTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
