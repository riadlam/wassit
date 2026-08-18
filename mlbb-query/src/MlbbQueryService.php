<?php

namespace Wasit\MlbbQuery;

use MobaGuides\MobileLegendsApi\Exceptions\HeroNotFoundException;
use MobaGuides\MobileLegendsApi\Exceptions\ImageNotFoundException;
use MobaGuides\MobileLegendsApi\Fetchers\Hero;
use MobaGuides\MobileLegendsApi\Fetchers\Image;
use MobaGuides\MobileLegendsApi\MobileLegends;

class MlbbQueryService
{
    private Hero $heroes;

    private Image $images;

    public function __construct(?Hero $heroes = null, ?Image $images = null)
    {
        $this->heroes = $heroes ?? MobileLegends::make(Hero::class);
        $this->images = $images ?? MobileLegends::make(Image::class);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listHeroes(): array
    {
        return $this->heroes->all()
            ->map(fn ($hero) => $this->normalizeListHero($hero))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getHero(int $id): array
    {
        try {
            $hero = $this->heroes->detail($id);
        } catch (HeroNotFoundException $exception) {
            throw new MlbbQueryException("Hero {$id} was not found.", 404, $exception);
        }

        return $this->normalizeDetailHero($id, $hero);
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

    public function getImageUrl(string $key): string
    {
        try {
            return $this->normalizeUrl($this->images->find($key));
        } catch (ImageNotFoundException $exception) {
            throw new MlbbQueryException("Image {$key} was not found.", 404, $exception);
        }
    }

    public function getHeroAvatarUrl(int $id): string
    {
        try {
            return $this->normalizeUrl($this->images->heroAvatar($id));
        } catch (ImageNotFoundException $exception) {
            throw new MlbbQueryException("Avatar for hero {$id} was not found.", 404, $exception);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeListHero(object $hero): array
    {
        $id = (int) ($hero->heroid ?? 0);

        return [
            'id' => $id,
            'name' => (string) ($hero->name ?? ''),
            'avatar_url' => $this->resolveHeroAvatar($id, $hero->key ?? null),
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
            'avatar_url' => $this->resolveHeroAvatar($id, $hero->cover_picture ?? null),
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

    private function resolveHeroAvatar(int $id, mixed $fallback = null): ?string
    {
        if (is_string($fallback) && $fallback !== '') {
            return $this->normalizeUrl($fallback);
        }

        if ($id <= 0) {
            return null;
        }

        try {
            return $this->getHeroAvatarUrl($id);
        } catch (MlbbQueryException) {
            return null;
        }
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
