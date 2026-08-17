<?php

namespace App\Filament\Resources\Games\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(100),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(100),
                TextInput::make('icon_url')
                    ->label('Icon URL')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Active on website')
                    ->helperText('Only active games appear on the marketplace.'),
            ]);
    }
}
