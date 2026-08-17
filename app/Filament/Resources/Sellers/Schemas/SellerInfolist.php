<?php

namespace App\Filament\Resources\Sellers\Schemas;

use App\Models\Seller;
use App\Support\SellerRanks;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SellerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('Name'),
                TextEntry::make('user.email')
                    ->label('Email'),
                TextEntry::make('wallet')
                    ->label('Wallet balance')
                    ->suffix(' DA')
                    ->numeric(decimalPlaces: 2),
                TextEntry::make('total_sales')
                    ->label('Total sales'),
                TextEntry::make('rating')
                    ->numeric(decimalPlaces: 1),
                IconEntry::make('verified')
                    ->boolean()
                    ->label('Verified'),
                TextEntry::make('ranks')
                    ->label('Assigned ranks')
                    ->state(fn (Seller $record): string => collect(SellerRanks::normalize($record->ranks))
                        ->map(fn (string $rank): string => SellerRanks::options()[$rank] ?? $rank)
                        ->join(', ')),
                TextEntry::make('accounts_count')
                    ->label('Listings'),
                TextEntry::make('bio')
                    ->placeholder('No bio')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
