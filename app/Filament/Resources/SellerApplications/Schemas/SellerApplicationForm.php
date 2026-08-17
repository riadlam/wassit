<?php

namespace App\Filament\Resources\SellerApplications\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SellerApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('admin_notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
