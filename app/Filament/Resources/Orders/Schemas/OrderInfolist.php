<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('Order ID'),
                TextEntry::make('buyer.name')
                    ->label('Buyer'),
                TextEntry::make('seller.user.name')
                    ->label('Seller'),
                TextEntry::make('account.title')
                    ->label('Account'),
                TextEntry::make('amount_dzd')
                    ->suffix(' DA')
                    ->numeric(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('delivery_status')
                    ->label('Delivery')
                    ->badge()
                    ->placeholder('—'),
                TextEntry::make('paid_at')
                    ->dateTime()
                    ->placeholder('Not paid'),
                TextEntry::make('sofizpayCibTransaction.status')
                    ->label('SofizPay status')
                    ->badge()
                    ->placeholder('—'),
                TextEntry::make('sofizpayCibTransaction.transaction_id')
                    ->label('Transaction ID')
                    ->placeholder('—'),
                TextEntry::make('sofizpayCibTransaction.create_response')
                    ->label('Create response')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : '—')
                    ->columnSpanFull(),
                TextEntry::make('sofizpayCibTransaction.last_check_response')
                    ->label('Last check response')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : '—')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
