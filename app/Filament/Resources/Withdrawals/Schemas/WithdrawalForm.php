<?php

namespace App\Filament\Resources\Withdrawals\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WithdrawalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('admin_notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
