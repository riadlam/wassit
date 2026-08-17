<?php

namespace App\Filament\Resources\Listings\Schemas;

use App\Models\Seller;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('seller_id')
                    ->label('Seller')
                    ->options(fn (): array => Seller::query()
                        ->with('user')
                        ->get()
                        ->sortBy(fn (Seller $seller): string => $seller->user?->name ?? '')
                        ->mapWithKeys(fn (Seller $seller): array => [
                            $seller->id => ($seller->user?->name ?? 'Unknown seller')
                                .' · '.($seller->user?->email ?? "Seller #{$seller->id}"),
                        ])
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('game_id')
                    ->label('Game')
                    ->relationship(name: 'game', titleAttribute: 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->rows(5)
                    ->maxLength(10000)
                    ->columnSpanFull(),
                TextInput::make('price_dzd')
                    ->label('Price')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->suffix('DA')
                    ->required(),
                Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'pending' => 'Pending',
                        'sold' => 'Sold',
                        'cancelled' => 'Cancelled',
                        'disabled' => 'Disabled',
                    ])
                    ->native(false)
                    ->required(),
                Repeater::make('images')
                    ->label('Listing images')
                    ->relationship()
                    ->schema([
                        FileUpload::make('url')
                            ->label('Image')
                            ->disk('public')
                            ->directory('account_images')
                            ->visibility('public')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(10240)
                            ->downloadable()
                            ->openable()
                            ->required(),
                    ])
                    ->minItems(1)
                    ->maxItems(10)
                    ->addActionLabel('Add image')
                    ->reorderable()
                    ->columnSpanFull()
                    ->helperText('Keep between 1 and 10 images. Removed images are deleted from storage after saving.'),
                Repeater::make('attributes')
                    ->label('Listing attributes')
                    ->relationship()
                    ->schema([
                        TextInput::make('attribute_key')
                            ->label('Attribute')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('attribute_value')
                            ->label('Value')
                            ->rows(2)
                            ->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Add attribute')
                    ->itemLabel(fn (array $state): ?string => $state['attribute_key'] ?? null)
                    ->collapsible()
                    ->columnSpanFull()
                    ->helperText('These values are shown as the account’s game details.'),
            ]);
    }
}
