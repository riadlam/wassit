<?php

namespace App\Filament\Resources\SofizPayCibTransactions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SofizPayCibTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order.id')
                    ->label('Order ID'),
                TextEntry::make('transaction_id')
                    ->placeholder('—'),
                TextEntry::make('cib_order_number')
                    ->placeholder('—'),
                TextEntry::make('amount_expected')
                    ->suffix(' DA')
                    ->numeric(decimalPlaces: 2),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('paid_at')
                    ->dateTime()
                    ->placeholder('—'),
                TextEntry::make('create_response')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : '—')
                    ->columnSpanFull(),
                TextEntry::make('last_check_response')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : '—')
                    ->columnSpanFull(),
            ]);
    }
}
