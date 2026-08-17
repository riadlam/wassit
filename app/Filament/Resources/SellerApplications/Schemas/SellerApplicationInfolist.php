<?php

namespace App\Filament\Resources\SellerApplications\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SellerApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('full_name'),
                TextEntry::make('email'),
                TextEntry::make('phone'),
                TextEntry::make('country'),
                TextEntry::make('business_name')
                    ->placeholder('—'),
                TextEntry::make('website')
                    ->placeholder('—'),
                TextEntry::make('experience'),
                TextEntry::make('games')
                    ->columnSpanFull(),
                TextEntry::make('preferred_location')
                    ->placeholder('—'),
                TextEntry::make('account_count')
                    ->label('Accounts to list'),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextEntry::make('admin_notes')
                    ->placeholder('No admin notes')
                    ->columnSpanFull(),
                TextEntry::make('user.name')
                    ->label('Linked user'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
