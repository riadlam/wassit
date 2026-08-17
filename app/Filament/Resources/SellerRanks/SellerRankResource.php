<?php

namespace App\Filament\Resources\SellerRanks;

use App\Filament\Resources\SellerRanks\Pages\ListSellerRanks;
use App\Filament\Resources\SellerRanks\Schemas\SellerRankForm;
use App\Filament\Resources\SellerRanks\Tables\SellerRanksTable;
use App\Models\Seller;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SellerRankResource extends Resource
{
    protected static ?string $model = Seller::class;

    protected static ?string $slug = 'ranks';

    protected static ?string $navigationLabel = 'Ranks';

    protected static ?string $modelLabel = 'Seller rank';

    protected static ?string $pluralModelLabel = 'Seller ranks';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static string|UnitEnum|null $navigationGroup = 'Users & Sellers';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('user')
            ->whereHas('user', fn (Builder $query) => $query->where('role', 'seller'));
    }

    public static function form(Schema $schema): Schema
    {
        return SellerRankForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SellerRanksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSellerRanks::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
