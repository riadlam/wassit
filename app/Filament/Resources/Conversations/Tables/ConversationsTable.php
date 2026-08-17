<?php

namespace App\Filament\Resources\Conversations\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('buyer.name')
                    ->label('Buyer')
                    ->searchable(),
                TextColumn::make('seller.user.name')
                    ->label('Seller')
                    ->searchable(),
                TextColumn::make('accountForSale.title')
                    ->label('Listing')
                    ->limit(30)
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('buyer_unread_count')
                    ->label('Buyer unread'),
                TextColumn::make('seller_unread_count')
                    ->label('Seller unread'),
                TextColumn::make('last_message_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'archived' => 'Archived',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('last_message_at', 'desc');
    }
}
