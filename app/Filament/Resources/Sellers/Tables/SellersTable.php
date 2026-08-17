<?php

namespace App\Filament\Resources\Sellers\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SellersTable
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
                IconColumn::make('verified')
                    ->boolean()
                    ->label('Verified'),
                TextColumn::make('wallet')
                    ->label('Wallet')
                    ->suffix(' DA')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('total_sales')
                    ->label('Sales')
                    ->sortable(),
                TextColumn::make('rating')
                    ->numeric(decimalPlaces: 1)
                    ->sortable(),
                TextColumn::make('accounts_count')
                    ->label('Listings')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
