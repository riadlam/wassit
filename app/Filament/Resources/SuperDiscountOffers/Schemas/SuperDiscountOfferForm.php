<?php

namespace App\Filament\Resources\SuperDiscountOffers\Schemas;

use App\Models\AccountForSale;
use App\Models\SuperDiscountOffer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class SuperDiscountOfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('account_id')
                    ->label('Account')
                    ->relationship(
                        name: 'account',
                        titleAttribute: 'title',
                        modifyQueryUsing: function (Builder $query, $livewire) {
                            $currentId = $livewire->record?->account_id;

                            $query
                                ->with('game')
                                ->where(function (Builder $inner) use ($currentId) {
                                    $inner->where('status', 'available');

                                    if ($currentId) {
                                        // Keep the currently linked account selectable even if it is no longer available.
                                        $inner->orWhere('accounts_for_sale.id', $currentId);
                                    }
                                })
                                ->orderByDesc('id');
                        },
                    )
                    ->getOptionLabelFromRecordUsing(function (AccountForSale $record): string {
                        $game = $record->game?->name ?? 'Unknown game';

                        return "#{$record->id} · {$game} · {$record->title} · {$record->price_dzd} DA";
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Only available listings can be featured. Each account can appear once.'),
                TextInput::make('compare_at_price')
                    ->label('Higher price (before discount)')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->suffix('DA')
                    ->rule(function ($get) {
                        return function (string $attribute, $value, $fail) use ($get): void {
                            $accountId = $get('account_id');
                            if (! $accountId) {
                                return;
                            }
                            $salePrice = (int) (AccountForSale::query()->whereKey($accountId)->value('price_dzd') ?? 0);
                            if ((int) $value <= $salePrice) {
                                $fail("Higher price must be greater than the listing sale price ({$salePrice} DA).");
                            }
                        };
                    })
                    ->helperText('Buyers see this struck through. The listing’s current price is the sale price they pay — no % is shown.'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->required()
                    ->default(fn () => ((int) SuperDiscountOffer::query()->max('sort_order')) + 1)
                    ->minValue(0)
                    ->helperText('Lower numbers appear first. You can also drag rows in the list.'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Inactive offers stay hidden even inside the date window.'),
                DateTimePicker::make('starts_at')
                    ->label('Starts at')
                    ->native(false)
                    ->seconds(false)
                    ->nullable(),
                DateTimePicker::make('ends_at')
                    ->label('Ends at')
                    ->native(false)
                    ->seconds(false)
                    ->nullable()
                    ->afterOrEqual('starts_at'),
                FileUpload::make('image_path')
                    ->label('Homepage card image (optional)')
                    ->disk('public')
                    ->directory('super-discounts')
                    ->visibility('public')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120)
                    ->columnSpanFull()
                    ->helperText('Optional. Leave empty to use the account listing poster (generated preview cover) on the homepage card.'),
            ]);
    }
}
