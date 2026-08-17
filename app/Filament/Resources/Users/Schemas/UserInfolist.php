<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email'),
                TextEntry::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'seller' => 'success',
                        default => 'gray',
                    }),
                TextEntry::make('buyerOrders_count')
                    ->label('Orders as buyer')
                    ->state(fn ($record) => $record->buyerOrders()->count()),
                TextEntry::make('sellerApplication.status')
                    ->label('Seller application')
                    ->placeholder('No application'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
