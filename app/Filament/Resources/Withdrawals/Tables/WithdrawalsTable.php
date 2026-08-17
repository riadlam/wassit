<?php

namespace App\Filament\Resources\Withdrawals\Tables;

use App\Services\Admin\WithdrawalService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use InvalidArgumentException;

class WithdrawalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('seller.user.name')
                    ->label('Seller')
                    ->searchable(),
                TextColumn::make('amount')
                    ->suffix(' DA')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('processed_at')
                    ->dateTime()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Admin notes')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data, WithdrawalService $service) {
                        try {
                            $service->approve($record, $data['admin_notes'] ?? null);

                            Notification::make()
                                ->title('Withdrawal approved')
                                ->body('Seller wallet was deducted automatically.')
                                ->success()
                                ->send();
                        } catch (InvalidArgumentException $exception) {
                            Notification::make()
                                ->title('Unable to approve withdrawal')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Admin notes')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data, WithdrawalService $service) {
                        $service->reject($record, $data['admin_notes'] ?? null);

                        Notification::make()
                            ->title('Withdrawal rejected')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
