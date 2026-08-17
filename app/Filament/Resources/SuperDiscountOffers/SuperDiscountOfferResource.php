<?php

namespace App\Filament\Resources\SuperDiscountOffers;

use App\Filament\Resources\SuperDiscountOffers\Pages\CreateSuperDiscountOffer;
use App\Filament\Resources\SuperDiscountOffers\Pages\EditSuperDiscountOffer;
use App\Filament\Resources\SuperDiscountOffers\Pages\ListSuperDiscountOffers;
use App\Filament\Resources\SuperDiscountOffers\Schemas\SuperDiscountOfferForm;
use App\Filament\Resources\SuperDiscountOffers\Tables\SuperDiscountOffersTable;
use App\Models\SuperDiscountOffer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SuperDiscountOfferResource extends Resource
{
    protected static ?string $model = SuperDiscountOffer::class;

    protected static ?string $navigationLabel = 'Super Discounts';

    protected static ?string $modelLabel = 'Super Discount';

    protected static ?string $pluralModelLabel = 'Super Discounts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['account.game']);
    }

    public static function form(Schema $schema): Schema
    {
        return SuperDiscountOfferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuperDiscountOffersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuperDiscountOffers::route('/'),
            'create' => CreateSuperDiscountOffer::route('/create'),
            'edit' => EditSuperDiscountOffer::route('/{record}/edit'),
        ];
    }
}
