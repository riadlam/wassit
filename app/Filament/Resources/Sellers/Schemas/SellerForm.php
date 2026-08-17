<?php

namespace App\Filament\Resources\Sellers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SellerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('wallet')
                    ->numeric()
                    ->prefix('DA')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Wallet changes are made through completed orders and approved withdrawals.')
                    ->required(),
                TextInput::make('rating')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(5)
                    ->step(0.1),
                TextInput::make('total_sales')
                    ->numeric()
                    ->minValue(0),
                Toggle::make('verified')
                    ->label('Verified seller'),
                Textarea::make('bio')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
