<?php

namespace App\Services;

use App\Models\MlbbEmote;
use App\Models\MlbbRecall;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MlbbCatalogSyncService
{
    public function __construct(
        private MlbbFandomService $fandom,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function listEmotes(): array
    {
        $rows = MlbbEmote::query()->orderBy('group')->orderBy('name')->get();
        $groups = [];

        foreach ($rows->groupBy(fn (MlbbEmote $row) => $row->group ?: 'Other') as $groupName => $items) {
            $emotes = $items->map(fn (MlbbEmote $row) => $this->emotePayload($row))->values()->all();
            $groups[] = [
                'group' => $groupName,
                'count' => count($emotes),
                'emotes' => $emotes,
            ];
        }

        $emotes = $rows->map(fn (MlbbEmote $row) => $this->emotePayload($row))->values()->all();

        return [
            'source' => 'database',
            'emote_count' => count($emotes),
            'group_count' => count($groups),
            'groups' => $groups,
            'emotes' => $emotes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function listRecalls(): array
    {
        $rows = MlbbRecall::query()->orderBy('group')->orderBy('name')->get();
        $groups = [];

        foreach ($rows->groupBy(fn (MlbbRecall $row) => $row->group ?: 'Other') as $groupName => $items) {
            $recalls = $items->map(fn (MlbbRecall $row) => $this->recallPayload($row))->values()->all();
            $groups[] = [
                'group' => $groupName,
                'count' => count($recalls),
                'recalls' => $recalls,
            ];
        }

        $recalls = $rows->map(fn (MlbbRecall $row) => $this->recallPayload($row))->values()->all();

        return [
            'source' => 'database',
            'recall_count' => count($recalls),
            'group_count' => count($groups),
            'groups' => $groups,
            'recalls' => $recalls,
        ];
    }

    /**
     * @return array{remote_count: int, db_count: int, added_count: int, added: array<int, string>, last_checked_at: string}
     */
    public function checkEmotes(bool $fresh = true): array
    {
        $remote = $this->fandom->getAllBattleEmotes($fresh);
        $added = [];

        foreach ($remote['emotes'] ?? [] as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $emote = MlbbEmote::updateOrCreate(
                ['slug' => $this->slug($name, (string) ($item['group'] ?? ''))],
                [
                    'name' => $name,
                    'group' => $item['group'] ?? null,
                    'description' => $item['description'] ?? null,
                    'heroes' => $item['heroes'] ?? [],
                    'thumbnail_url' => $item['thumbnail_url'] ?? null,
                    'image_url' => $item['image_url'] ?? null,
                ]
            );

            if ($emote->wasRecentlyCreated) {
                $added[] = $name;
            }
        }

        $result = [
            'remote_count' => count($remote['emotes'] ?? []),
            'db_count' => MlbbEmote::count(),
            'added_count' => count($added),
            'added' => $added,
            'last_checked_at' => now()->toIso8601String(),
        ];

        Cache::put('mlbb.catalog.last_check.emotes', $result, now()->addYear());

        return $result;
    }

    /**
     * @return array{remote_count: int, db_count: int, added_count: int, added: array<int, string>, last_checked_at: string}
     */
    public function checkRecalls(bool $fresh = true): array
    {
        $remote = $this->fandom->getAllRecallEffects($fresh);
        $added = [];

        foreach ($remote['recalls'] ?? [] as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $recall = MlbbRecall::updateOrCreate(
                ['slug' => $this->slug($name, (string) ($item['group'] ?? ''))],
                [
                    'name' => $name,
                    'group' => $item['group'] ?? null,
                    'description' => $item['description'] ?? null,
                    'thumbnail_url' => $item['thumbnail_url'] ?? null,
                    'image_url' => $item['image_url'] ?? null,
                ]
            );

            if ($recall->wasRecentlyCreated) {
                $added[] = $name;
            }
        }

        $result = [
            'remote_count' => count($remote['recalls'] ?? []),
            'db_count' => MlbbRecall::count(),
            'added_count' => count($added),
            'added' => $added,
            'last_checked_at' => now()->toIso8601String(),
        ];

        Cache::put('mlbb.catalog.last_check.recalls', $result, now()->addYear());

        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function lastCheck(string $type): ?array
    {
        $cached = Cache::get('mlbb.catalog.last_check.'.$type);

        return is_array($cached) ? $cached : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function emotePayload(MlbbEmote $row): array
    {
        return [
            'id' => $row->id,
            'name' => $row->name,
            'description' => $row->description,
            'heroes' => $row->heroes ?? [],
            'group' => $row->group,
            'thumbnail_url' => $row->thumbnail_url,
            'image_url' => $row->image_url,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function recallPayload(MlbbRecall $row): array
    {
        return [
            'id' => $row->id,
            'name' => $row->name,
            'description' => $row->description,
            'group' => $row->group,
            'thumbnail_url' => $row->thumbnail_url,
            'image_url' => $row->image_url,
        ];
    }

    private function slug(string $name, string $group): string
    {
        $slug = Str::slug($name);
        if ($slug === '') {
            $slug = Str::slug($group.'-'.$name) ?: md5(mb_strtolower($name));
        }

        return $slug;
    }
}
