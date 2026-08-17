<?php

namespace App\Filament\Resources\Withdrawals\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WithdrawalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('seller.user.name')
                    ->label('Seller'),
                TextEntry::make('seller.user.email')
                    ->label('Email'),
                TextEntry::make('seller.wallet')
                    ->label('Current wallet')
                    ->suffix(' DA')
                    ->numeric(decimalPlaces: 2),
                TextEntry::make('amount')
                    ->suffix(' DA')
                    ->numeric(decimalPlaces: 2),
                TextEntry::make('payment_method')
                    ->placeholder('—'),
                TextEntry::make('payment_details')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : '—')
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextEntry::make('admin_notes')
                    ->placeholder('—')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('processed_at')
                    ->dateTime()
                    ->placeholder('—'),
            ]);
    }
}
