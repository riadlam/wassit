<?php

namespace App\Filament\Resources\Conversations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ConversationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('buyer.name')
                    ->label('Buyer'),
                TextEntry::make('seller.user.name')
                    ->label('Seller'),
                TextEntry::make('accountForSale.title')
                    ->label('Listing')
                    ->placeholder('—'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('buyer_unread_count')
                    ->label('Buyer unread'),
                TextEntry::make('seller_unread_count')
                    ->label('Seller unread'),
                TextEntry::make('last_message_at')
                    ->dateTime()
                    ->placeholder('—'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
