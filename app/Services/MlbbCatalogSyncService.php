<?php

namespace App\Services;

use App\Models\MlbbEmote;
use App\Models\MlbbRecall;
use Illuminate\Database\Eloquent\Model;
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
        /** @var array<int, true> $claimed */
        $claimed = [];

        foreach ($remote['emotes'] ?? [] as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $group = isset($item['group']) ? trim((string) $item['group']) : '';
            $group = $group !== '' ? $group : null;
            $imageUrl = $item['image_url'] ?? null;
            $thumbnailUrl = $item['thumbnail_url'] ?? null;
            $slug = $this->slug($name, (string) ($group ?? ''), $imageUrl);

            $emote = $this->findCatalogRow(
                MlbbEmote::class,
                $name,
                $group,
                $imageUrl,
                $slug,
                $claimed,
            );

            $payload = [
                'slug' => $this->uniqueSlug(MlbbEmote::class, $slug, $emote?->id),
                'name' => $name,
                'group' => $group,
                'description' => $item['description'] ?? null,
                'heroes' => $item['heroes'] ?? [],
                'thumbnail_url' => $thumbnailUrl,
                'image_url' => $imageUrl,
            ];

            if ($emote) {
                $emote->fill($payload)->save();
            } else {
                $emote = MlbbEmote::create($payload);
                $added[] = $group ? "{$name} ({$group})" : $name;
            }

            $claimed[(int) $emote->id] = true;
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
        /** @var array<int, true> $claimed */
        $claimed = [];

        foreach ($remote['recalls'] ?? [] as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $group = isset($item['group']) ? trim((string) $item['group']) : '';
            $group = $group !== '' ? $group : null;
            $imageUrl = $item['image_url'] ?? null;
            $thumbnailUrl = $item['thumbnail_url'] ?? null;
            $slug = $this->slug($name, (string) ($group ?? ''), $imageUrl);

            $recall = $this->findCatalogRow(
                MlbbRecall::class,
                $name,
                $group,
                $imageUrl,
                $slug,
                $claimed,
            );

            $payload = [
                'slug' => $this->uniqueSlug(MlbbRecall::class, $slug, $recall?->id),
                'name' => $name,
                'group' => $group,
                'description' => $item['description'] ?? null,
                'thumbnail_url' => $thumbnailUrl,
                'image_url' => $imageUrl,
            ];

            if ($recall) {
                $recall->fill($payload)->save();
            } else {
                $recall = MlbbRecall::create($payload);
                $added[] = $group ? "{$name} ({$group})" : $name;
            }

            $claimed[(int) $recall->id] = true;
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

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, true>  $claimed
     */
    private function findCatalogRow(
        string $modelClass,
        string $name,
        ?string $group,
        ?string $imageUrl,
        string $slug,
        array $claimed,
    ): ?Model {
        $bySlug = $modelClass::query()->where('slug', $slug)->first();
        if ($bySlug && ! isset($claimed[(int) $bySlug->id])) {
            return $bySlug;
        }

        $byImage = null;
        if ($imageUrl) {
            $byImage = $modelClass::query()
                ->where('name', $name)
                ->where('image_url', $imageUrl)
                ->when(
                    $group === null,
                    fn ($q) => $q->whereNull('group'),
                    fn ($q) => $q->where('group', $group),
                )
                ->first();
        }

        if ($byImage && ! isset($claimed[(int) $byImage->id])) {
            return $byImage;
        }

        $sameNameGroup = $modelClass::query()
            ->where('name', $name)
            ->when(
                $group === null,
                fn ($q) => $q->whereNull('group'),
                fn ($q) => $q->where('group', $group),
            )
            ->get()
            ->filter(fn (Model $row) => ! isset($claimed[(int) $row->id]))
            ->values();

        if ($sameNameGroup->count() === 1) {
            return $sameNameGroup->first();
        }

        // Legacy rows used name-only slugs and collapsed same titles across years.
        $legacySlug = Str::slug($name);
        if ($legacySlug !== '') {
            $legacy = $modelClass::query()
                ->where('slug', $legacySlug)
                ->get()
                ->filter(fn (Model $row) => ! isset($claimed[(int) $row->id]))
                ->values();

            if ($legacy->count() === 1) {
                $row = $legacy->first();
                $rowGroup = $row->getAttribute('group');
                if ($rowGroup === null || $rowGroup === $group) {
                    return $row;
                }
            }
        }

        return null;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function uniqueSlug(string $modelClass, string $slug, ?int $ignoreId = null): string
    {
        $base = $slug !== '' ? $slug : 'item';
        $candidate = $base;
        $i = 2;

        while (
            $modelClass::query()
                ->where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        return $candidate;
    }

    private function slug(string $name, string $group, ?string $imageUrl = null): string
    {
        $parts = array_values(array_filter([
            Str::slug($group),
            Str::slug($name),
        ]));

        $base = implode('-', $parts);
        if ($base === '') {
            $base = 'item';
        }

        if ($imageUrl) {
            $path = (string) (parse_url($imageUrl, PHP_URL_PATH) ?: $imageUrl);
            $base .= '-'.substr(sha1(mb_strtolower($path)), 0, 8);
        }

        return $base;
    }
}
