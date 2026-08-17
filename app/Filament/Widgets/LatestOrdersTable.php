<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestOrdersTable extends TableWidget
{
    protected static ?string $heading = 'Latest Orders';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['buyer', 'seller.user', 'account'])
                    ->latest()
                    ->limit(10)
            )
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
                    ->numeric(),
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
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
