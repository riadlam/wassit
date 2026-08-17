<?php

namespace App\Filament\Widgets;

use App\Models\SellerApplication;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestApplicationsTable extends TableWidget
{
    protected static ?string $heading = 'Latest Seller Applications';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SellerApplication::query()
                    ->with('user')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('full_name')
                    ->label('Applicant')
                    ->searchable(),
                TextColumn::make('email'),
                TextColumn::make('phone'),
                TextColumn::make('country'),
                TextColumn::make('games')
                    ->limit(30),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime(),
            ])
            ->paginated(false);
    }
}
