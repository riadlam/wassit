<?php

namespace App\Filament\Resources\Listings\Tables;

use App\Models\AccountForSale;
use App\Models\Seller;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Action::make('viewOnSite')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (AccountForSale $record): string => route('accounts.show', [
                        'slug' => $record->game?->publicSlug() ?? 'game',
                        'id' => $record->id,
                    ]))
                    ->openUrlInNewTab()
                    ->disabled(fn (AccountForSale $record): bool => $record->status !== 'available')
                    ->tooltip(fn (AccountForSale $record): ?string => $record->status === 'available'
                        ? 'Open the public account page'
                        : 'Only available listings have a public account page'),
                EditAction::make()
                    ->modalHeading(fn (AccountForSale $record): string => "Edit listing #{$record->id}")
                    ->modalSubmitActionLabel('Save listing')
                    ->modalWidth(Width::SevenExtraLarge),
                DeleteAction::make()
                    ->modalHeading(fn (AccountForSale $record): string => "Delete listing #{$record->id}?")
                    ->modalDescription('This permanently deletes the listing, its images, attributes, and any super discount attached to it.')
                    ->disabled(fn (AccountForSale $record): bool => (int) $record->orders_count > 0)
                    ->tooltip(fn (AccountForSale $record): ?string => (int) $record->orders_count > 0
                        ? 'Listings with order history cannot be deleted. Set the status to Disabled instead.'
                        : 'Permanently delete this listing')
                    ->failureNotificationTitle('This listing has order history and cannot be deleted.')
                    ->using(function (AccountForSale $record): bool {
                        return DB::transaction(function () use ($record): bool {
                            $listing = AccountForSale::query()
                                ->whereKey($record->id)
                                ->lockForUpdate()
                                ->firstOrFail();

                            if ($listing->orders()->exists()) {
                                return false;
                            }

                            $listing->images()->get()->each->delete();

                            $offer = $listing->superDiscountOffer()->first();
                            if ($offer) {
                                Storage::disk('public')->delete((string) $offer->image_path);
                                $offer->delete();
                            }

                            return (bool) $listing->delete();
                        });
                    }),
            ])
            ->recordAction('edit')
            ->defaultSort('created_at', 'desc');
    }
}
