<?php

namespace App\Http\Controllers;

use App\Services\MlbbApiService;
use App\Services\MlbbFandomService;
use App\Services\MlbbSkinCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class MlbbPlaygroundController extends Controller
{
    public function __construct(
        private MlbbApiService $mlbb,
        private MlbbFandomService $fandom,
        private MlbbSkinCatalogService $skinCatalog,
    ) {}

    public function index(): View
    {
        return view('mlbb.playground', [
            'title' => 'MLBB Skins Playground',
        ]);
    }

    public function heroes(): JsonResponse
    {
        return $this->respond(fn () => [
            'heroes' => $this->mlbb->listHeroes(),
            'source' => 'mapi.mobilelegends.com (hero list) + fandom (skins)',
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = (string) $request->query('q', '');

        return $this->respond(fn () => [
            'query' => $query,
            'heroes' => $this->mlbb->searchHeroes($query),
        ]);
    }

    public function show(string $hero): JsonResponse
    {
        $heroName = urldecode($hero);

        return $this->respond(function () use ($heroName) {
            $local = $this->skinCatalog->heroPayload($heroName);
            if ($local) {
                return ['hero' => $local];
            }

            return ['hero' => $this->fandom->getHeroSkins($heroName)];
        });
    }

    public function heroEmotes(string $hero): JsonResponse
    {
        $heroName = urldecode($hero);

        return $this->respond(fn () => [
            'hero' => $this->fandom->getHeroBattleEmotes($heroName),
        ]);
    }

    public function emotes(): JsonResponse
    {
        return $this->respond(fn () => $this->fandom->getAllBattleEmotes());
    }

    public function recalls(): JsonResponse
    {
        return $this->respond(fn () => $this->fandom->getAllRecallEffects());
    }

    public function sampleSkins(Request $request): JsonResponse
    {
        $count = min(12, max(1, (int) $request->query('count', 8)));

        return $this->respond(function () use ($count) {
            $local = $this->skinCatalog->sampleSkins($count);
            if ($local !== []) {
                return [
                    'skins' => $local,
                    'source' => 'local',
                ];
            }

            $heroPool = ['Gusion', 'Hayabusa', 'Granger', 'Miya', 'Dyrroth', 'Saber', 'Chang\'e', 'Ruby', 'Natalia', 'Lancelot'];
            shuffle($heroPool);

            $skins = [];

            foreach ($heroPool as $heroName) {
                if (count($skins) >= $count) {
                    break;
                }

                try {
                    $hero = $this->fandom->getHeroSkins($heroName);
                } catch (\Throwable) {
                    continue;
                }

                $candidates = array_values(array_filter(
                    array_slice($hero['skins'] ?? [], 2),
                    fn (array $skin) => ! empty($skin['image_url']) || ! empty($skin['thumbnail_url'])
                ));

                if ($candidates === []) {
                    continue;
                }

                shuffle($candidates);

                foreach ($candidates as $skin) {
                    $skins[] = [
                        'hero' => $heroName,
                        'name' => $skin['name'] ?? 'Skin',
                        'rarity' => $skin['rarity'] ?? 'Skin',
                        'image_url' => $skin['image_url'] ?? null,
                        'thumbnail_url' => $skin['thumbnail_url'] ?? null,
                        'tags' => $skin['tags'] ?? [],
                        'painted' => ! empty($skin['painted']),
                    ];

                    if (count($skins) >= $count) {
                        break;
                    }
                }
            }

            shuffle($skins);

            return [
                'skins' => array_slice($skins, 0, $count),
                'source' => 'fandom-cache',
            ];
        });
    }

    private function respond(callable $callback): JsonResponse
    {
        try {
            return response()->json($callback());
        } catch (RuntimeException $exception) {
            $status = $exception->getCode() >= 400 ? $exception->getCode() : 404;

            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Failed to fetch Fandom wiki data.',
            ], 500);
        }
    }
}
