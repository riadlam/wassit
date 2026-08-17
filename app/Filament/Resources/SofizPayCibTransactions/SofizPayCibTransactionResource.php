<?php

namespace App\Filament\Resources\SofizPayCibTransactions;

use App\Filament\Resources\SofizPayCibTransactions\Pages\ListSofizPayCibTransactions;
use App\Filament\Resources\SofizPayCibTransactions\Pages\ViewSofizPayCibTransaction;
use App\Filament\Resources\SofizPayCibTransactions\Schemas\SofizPayCibTransactionInfolist;
use App\Filament\Resources\SofizPayCibTransactions\Tables\SofizPayCibTransactionsTable;
use App\Models\SofizPayCibTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SofizPayCibTransactionResource extends Resource
{
    protected static ?string $model = SofizPayCibTransaction::class;

    protected static ?string $navigationLabel = 'Payment Records';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Payments & Wallets';

    protected static ?int $navigationSort = 3;

    public static function infolist(Schema $schema): Schema
    {
        return SofizPayCibTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SofizPayCibTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSofizPayCibTransactions::route('/'),
            'view' => ViewSofizPayCibTransaction::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
