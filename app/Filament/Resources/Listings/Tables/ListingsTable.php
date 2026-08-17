<?php

namespace App\Filament\Resources\Listings\Tables;

use App\Models\AccountForSale;
use App\Models\Seller;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                ImageColumn::make('preview')
                    ->label('Image')
                    ->state(fn (AccountForSale $record): ?string => $record->images->first()?->url)
                    ->disk('public')
                    ->square(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(45),
                TextColumn::make('seller.user.name')
                    ->label('Seller')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('game.name')
                    ->label('Game')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price_dzd')
                    ->label('Price')
                    ->suffix(' DA')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'pending' => 'warning',
                        'sold' => 'info',
                        'cancelled' => 'danger',
                        'disabled' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('images_count')
                    ->label('Images')
                    ->counts('images'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'available' => 'Available',
                        'pending' => 'Pending',
                        'sold' => 'Sold',
                        'cancelled' => 'Cancelled',
                        'disabled' => 'Disabled',
                    ]),
                SelectFilter::make('game')
                    ->relationship('game', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('seller_id')
                    ->label('Seller')
                    ->options(fn (): array => Seller::query()
                        ->with('user')
                        ->get()
                        ->sortBy(fn (Seller $seller): string => $seller->user?->name ?? '')
                        ->mapWithKeys(fn (Seller $seller): array => [
                            $seller->id => $seller->user?->name ?? "Seller #{$seller->id}",
                        ])
                        ->all())
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading(fn (AccountForSale $record): string => "Edit listing #{$record->id}")
                    ->modalSubmitActionLabel('Save listing')
                    ->modalWidth(Width::SevenExtraLarge),
            ])
            ->recordAction('edit')
            ->defaultSort('created_at', 'desc');
    }
}
