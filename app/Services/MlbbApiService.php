<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MlbbApiService
{
    private const HERO_LIST_ENDPOINT = 'https://mapi.mobilelegends.com/hero/list';

    private const HERO_DETAIL_ENDPOINT = 'https://mapi.mobilelegends.com/hero/detail?id=%s';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listHeroes(): array
    {
        $response = $this->getJson(self::HERO_LIST_ENDPOINT);

        $heroes = collect($response->data ?? [])
            ->map(fn ($hero) => $this->normalizeListHero($hero))
            ->filter(fn (array $hero) => $hero['name'] !== '')
            ->values();

        $known = $heroes
            ->mapWithKeys(fn (array $hero) => [mb_strtolower($hero['name']) => true])
            ->all();

        foreach (config('mlbb.supplemental_heroes', []) as $extra) {
            $name = trim((string) ($extra['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $key = mb_strtolower($name);
            if (isset($known[$key])) {
                continue;
            }

            $heroes->push([
                'id' => (int) ($extra['id'] ?? 0),
                'name' => $name,
                'avatar_url' => $this->normalizeUrl($extra['avatar_url'] ?? null),
            ]);
            $known[$key] = true;
        }

        return $heroes
            ->sortBy(fn (array $hero) => mb_strtolower($hero['name']), SORT_NATURAL)
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getHero(int $id): array
    {
        $response = $this->getJson(sprintf(self::HERO_DETAIL_ENDPOINT, $id));

        if (! isset($response->data->name)) {
            throw new RuntimeException("Hero {$id} was not found.", 404);
        }

        return $this->normalizeDetailHero($id, $response->data);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchHeroes(string $query): array
    {
        $needle = mb_strtolower(trim($query));

        if ($needle === '') {
            return [];
        }

        return collect($this->listHeroes())
            ->filter(fn (array $hero) => str_contains(mb_strtolower($hero['name']), $needle))
            ->values()
            ->all();
    }

    private function getJson(string $url): object
    {
        $response = Http::timeout(20)
            ->retry(2, 200)
            ->get($url);

        if ($response->failed()) {
            throw new RuntimeException('Failed to fetch MLBB data.', 502);
        }

        return $response->object() ?? (object) [];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeListHero(object $hero): array
    {
        return [
            'id' => (int) ($hero->heroid ?? 0),
            'name' => (string) ($hero->name ?? ''),
            'avatar_url' => $this->normalizeUrl($hero->key ?? null),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeDetailHero(int $id, object $hero): array
    {
        $skills = [];
        foreach ($hero->skill->skill ?? [] as $skill) {
            $skills[] = [
                'name' => (string) ($skill->name ?? ''),
                'icon' => $this->normalizeUrl($skill->icon ?? null),
                'description' => (string) ($skill->des ?? ''),
                'tips' => (string) ($skill->tips ?? ''),
            ];
        }

        $equipment = [];
        foreach ($hero->gear->out_pack ?? [] as $item) {
            $equipment[] = [
                'equipment_id' => (int) ($item->equipment_id ?? 0),
                'name' => (string) ($item->equip->name ?? ''),
                'icon' => $this->normalizeUrl($item->equip->icon ?? null),
                'description' => array_values(array_filter((array) ($item->equip->des ?? []))),
            ];
        }

        return [
            'id' => $id,
            'name' => (string) ($hero->name ?? ''),
            'role' => (string) ($hero->type ?? ''),
            'avatar_url' => $this->normalizeUrl($hero->cover_picture ?? null),
            'gallery_url' => $this->normalizeUrl($hero->gallery_picture ?? null),
            'stats' => [
                'physical' => (int) ($hero->phy ?? 0),
                'magic' => (int) ($hero->mag ?? 0),
                'survivability' => (int) ($hero->alive ?? 0),
                'difficulty' => (int) ($hero->diff ?? 0),
            ],
            'skills' => $skills,
            'recommended_equipment' => $equipment,
            'equipment_tips' => (string) ($hero->gear->out_pack_tips ?? ''),
            'counters' => $this->normalizeCounters($hero->counters ?? null),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeCounters(?object $counters): array
    {
        if ($counters === null) {
            return [];
        }

        $normalizeEntry = function (?object $entry, string $tipsKey): ?array {
            if ($entry === null) {
                return null;
            }

            return [
                'hero_id' => (int) ($entry->heroid ?? 0),
                'name' => (string) ($entry->name ?? ''),
                'icon' => $this->normalizeUrl($entry->icon ?? null),
                'tips' => (string) ($entry->{$tipsKey} ?? ''),
            ];
        };

        return array_filter([
            'best_teammate' => $normalizeEntry($counters->best ?? null, 'best_mate_tips'),
            'counters' => $normalizeEntry($counters->counters ?? null, 'restrain_hero_tips'),
            'countered_by' => $normalizeEntry($counters->countered ?? null, 'by_restrain_tips'),
        ]);
    }

    private function normalizeUrl(mixed $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        return $url;
    }
}
