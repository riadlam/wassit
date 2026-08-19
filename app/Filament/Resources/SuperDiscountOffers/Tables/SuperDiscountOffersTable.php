<?php

namespace App\Filament\Resources\SuperDiscountOffers\Tables;

use App\Models\SuperDiscountOffer;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SuperDiscountOffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Artwork')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(fn (SuperDiscountOffer $record): string => $record->accountCoverImageUrl()),
                TextColumn::make('account.title')
                    ->label('Account')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('account.game.name')
                    ->label('Game'),
                TextColumn::make('discount_percentage')
                    ->label('Off')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('account.price_dzd')
                    ->label('Original')
                    ->suffix(' DA')
                    ->numeric(),
                TextColumn::make('sale_price')
                    ->label('Sale')
                    ->state(fn (SuperDiscountOffer $record): int => $record->discountedPrice((int) ($record->account?->price_dzd ?? 0)))
                    ->suffix(' DA')
                    ->numeric(),
                TextColumn::make('sort_order')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->placeholder('Always'),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->placeholder('Open'),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}
