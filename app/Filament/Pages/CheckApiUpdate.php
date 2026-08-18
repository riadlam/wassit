<?php

namespace App\Filament\Pages;

use App\Models\MlbbEmote;
use App\Models\MlbbRecall;
use App\Services\MlbbCatalogSyncService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class CheckApiUpdate extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?string $navigationLabel = 'Check API Update';

    protected static ?string $title = 'Check API Update';

    protected static ?string $slug = 'check-api-update';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.check-api-update';

    public int $emoteDbCount = 0;

    public int $recallDbCount = 0;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $emoteResult = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $recallResult = null;

    public function mount(MlbbCatalogSyncService $catalog): void
    {
        $this->emoteDbCount = MlbbEmote::count();
        $this->recallDbCount = MlbbRecall::count();
        $this->emoteResult = $catalog->lastCheck('emotes');
        $this->recallResult = $catalog->lastCheck('recalls');
    }

    public function checkEmotes(): void
    {
        set_time_limit(180);
        $catalog = app(MlbbCatalogSyncService::class);
        $this->emoteResult = $catalog->checkEmotes(true);
        $this->emoteDbCount = (int) $this->emoteResult['db_count'];
        $this->notifyResult('Emotes', $this->emoteResult);
    }

    public function checkRecalls(): void
    {
        set_time_limit(180);
        $catalog = app(MlbbCatalogSyncService::class);
        $this->recallResult = $catalog->checkRecalls(true);
        $this->recallDbCount = (int) $this->recallResult['db_count'];
        $this->notifyResult('Recalls', $this->recallResult);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('checkEmotes')
                ->label('Check Emotes')
                ->icon(Heroicon::OutlinedFaceSmile)
                ->action(fn () => $this->checkEmotes()),
            Action::make('checkRecalls')
                ->label('Check Recalls')
                ->icon(Heroicon::OutlinedSparkles)
                ->color('gray')
                ->action(fn () => $this->checkRecalls()),
        ];
    }

    /**
     * @param  array{added_count: int, remote_count: int, added: array<int, string>}  $result
     */
    private function notifyResult(string $label, array $result): void
    {
        $added = (int) $result['added_count'];
        $body = $added > 0
            ? $added.' new '.$label.' saved: '.implode(', ', array_slice($result['added'], 0, 8)).(count($result['added']) > 8 ? '…' : '')
            : 'No new '.$label.'. Wiki has '.$result['remote_count'].' items.';

        Notification::make()
            ->title($label.' check complete')
            ->body($body)
            ->success()
            ->send();
    }
}
