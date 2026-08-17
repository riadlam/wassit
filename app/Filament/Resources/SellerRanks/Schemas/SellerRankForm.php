<?php

namespace App\Filament\Resources\SellerRanks\Schemas;

use App\Support\SellerRanks;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;

class SellerRankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('seller_name')
                    ->label('Seller')
                    ->content(fn ($record): string => $record?->user?->name ?? 'Unknown seller'),
                Placeholder::make('seller_email')
                    ->label('Email')
                    ->content(fn ($record): string => $record?->user?->email ?? '—'),
                CheckboxList::make('ranks')
                    ->label('Assigned ranks')
                    ->options(fn (): array => SellerRanks::options())
                    ->default(['elite'])
                    ->required()
                    ->minItems(1)
                    ->columns(2)
                    ->bulkToggleable()
                    ->helperText('Elite Seller is the default. Select every badge that should appear on this seller’s listings.')
                    ->columnSpanFull(),
            ]);
    }
}
