<?php

namespace App\Filament\Resources\Listings;

use App\Filament\Resources\Listings\Pages\ListListings;
use App\Filament\Resources\Listings\Schemas\ListingForm;
use App\Filament\Resources\Listings\Tables\ListingsTable;
use App\Models\AccountForSale;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ListingResource extends Resource
{
    protected static ?string $model = AccountForSale::class;

    protected static ?string $slug = 'listings';

    protected static ?string $navigationLabel = 'Listings';

    protected static ?string $modelLabel = 'Listing';

    protected static ?string $pluralModelLabel = 'Listings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['seller.user', 'game', 'images'])
            ->withCount('orders');
    }

    public static function form(Schema $schema): Schema
    {
        return ListingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ListingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListListings::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
