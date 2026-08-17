<?php

namespace App\Filament\Resources\SellerApplications;

use App\Filament\Resources\SellerApplications\Pages\ListSellerApplications;
use App\Filament\Resources\SellerApplications\Pages\ViewSellerApplication;
use App\Filament\Resources\SellerApplications\Schemas\SellerApplicationForm;
use App\Filament\Resources\SellerApplications\Schemas\SellerApplicationInfolist;
use App\Filament\Resources\SellerApplications\Tables\SellerApplicationsTable;
use App\Models\SellerApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SellerApplicationResource extends Resource
{
    protected static ?string $model = SellerApplication::class;

    protected static ?string $navigationLabel = 'Pending Applications';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Users & Sellers';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getNavigationBadge(): ?string
    {
        $count = SellerApplication::query()->where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return SellerApplicationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SellerApplicationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SellerApplicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSellerApplications::route('/'),
            'view' => ViewSellerApplication::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
