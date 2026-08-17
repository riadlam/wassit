<?php

namespace App\Filament\Resources\SellerRanks\Tables;

use App\Models\Seller;
use App\Support\SellerRanks;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SellerRanksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Seller')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('ranks')
                    ->label('Current ranks')
                    ->state(fn (Seller $record): array => collect(SellerRanks::normalize($record->ranks))
                        ->map(fn (string $rank): string => SellerRanks::options()[$rank] ?? $rank)
                        ->all())
                    ->badge()
                    ->separator(','),
                TextColumn::make('total_sales')
                    ->label('Sales')
                    ->sortable(),
                TextColumn::make('rating')
                    ->numeric(decimalPlaces: 1)
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Assign ranks')
                    ->modalHeading(fn (Seller $record): string => 'Ranks for '.($record->user?->name ?? 'seller')),
            ])
            ->recordAction('edit')
            ->defaultSort('id');
    }
}
