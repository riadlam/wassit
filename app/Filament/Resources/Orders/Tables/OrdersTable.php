<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('buyer.name')
                    ->label('Buyer')
                    ->searchable(),
                TextColumn::make('seller.user.name')
                    ->label('Seller')
                    ->searchable(),
                TextColumn::make('account.title')
                    ->label('Account')
                    ->limit(30),
                TextColumn::make('amount_dzd')
                    ->label('Amount')
                    ->suffix(' DA')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'cancelled', 'refunded' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('delivery_status')
                    ->label('Delivery')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('sofizpayCibTransaction.status')
                    ->label('Payment')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Succeeded',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                        'in_progress' => 'In progress',
                    ]),
                SelectFilter::make('payment_outcome')
                    ->label('Payment outcome')
                    ->options([
                        'succeeded' => 'Succeeded',
                        'pending' => 'Pending',
                        'failed' => 'Failed attempt',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'succeeded' => $query->where('status', 'completed'),
                            'pending' => $query->where('status', 'pending')
                                ->where(function ($q) {
                                    $q->whereDoesntHave('sofizpayCibTransaction')
                                        ->orWhereHas('sofizpayCibTransaction', fn ($t) => $t->where('status', 'pending'));
                                }),
                            'failed' => $query->where('status', 'pending')
                                ->whereHas('sofizpayCibTransaction', fn ($t) => $t->where('status', '!=', 'paid')),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
