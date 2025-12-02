<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\AccountForSale;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
// SkinsHelper no longer needed for pure ID filters

class GameController extends Controller
{
    public function index()
    {
        // TODO: Return list of all games
    }

    public function getAttributes($id)
    {
        // TODO: Return static attributes for a specific game
    }

    public function debugSkins($slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        $accounts = AccountForSale::where('game_id', $game->id)
            ->where('status', 'available')
            ->with(['attributes'])
            ->get();
            
        $debugInfo = [];
        foreach ($accounts as $account) {
            $debugInfo[] = [
                'account_id' => $account->id,
                'title' => $account->title,
                'attributes' => $account->attributes->pluck('attribute_value', 'attribute_key')->toArray()
            ];
        }
        
        return response()->json([
            'game' => $game->name,
            'total_accounts' => $accounts->count(),
            'accounts' => $debugInfo
        ]);
    }

    public function filterAccounts(Request $request, $slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();

        try {
            $query = QueryBuilder::for(AccountForSale::class)
                ->where('game_id', $game->id)
                ->where('status', 'available')
                ->with(['seller.user', 'attributes', 'images'])
                ->with(['seller' => function ($q) {
                    $q->withCount(['orders' => function ($oq) {
                        $oq->where('status', 'completed');
                    }]);
                }])
                ->allowedFilters([
                    AllowedFilter::callback('search', function ($query, $value) {
                        $query->where(function($q) use ($value) {
                            $q->where('title', 'like', "%{$value}%")
                              ->orWhere('description', 'like', "%{$value}%");
                        });
                    }),
                    AllowedFilter::callback('rank', function ($query, $value) {
                        $query->whereHas('attributes', function($q) use ($value) {
                            $q->where('attribute_key', 'rank')
                              ->where('attribute_value', 'like', "%{$value}%");
                        });
                    }),
                    AllowedFilter::callback('collection', function ($query, $value) {
                        $query->whereHas('attributes', function($q) use ($value) {
                            $q->where('attribute_key', 'collection_tier')
                              ->where('attribute_value', '=', $value);
                        });
                    }),
                    AllowedFilter::callback('platform', function ($query, $value) {
                        $query->whereHas('attributes', function($q) use ($value) {
                            $q->where('attribute_key', 'platform');
                            if ($value === 'both') {
                                $q->where('attribute_value', 'like', '%iOS%')
                                  ->where('attribute_value', 'like', '%Android%');
                            } else {
                                $q->where('attribute_value', 'like', "%{$value}%");
                            }
                        });
                    }),
                    AllowedFilter::callback('price', function ($query, $value) {
                        if (strpos($value, '-') !== false) {
                            [$min, $max] = explode('-', $value);
                            $query->whereBetween('price_dzd', [(int)$min, (int)$max]);
                        } elseif (strpos($value, '+') !== false) {
                            $min = (int)str_replace('+', '', $value);
                            $query->where('price_dzd', '>=', $min);
                        }
                    }),
                    // Skins filter using LIKE token matching on comma-separated IDs
                    AllowedFilter::callback('skins', function ($query, $value) {
                        try {
                            if ($value === null || $value === '') return;
                            $vals = is_array($value) ? $value : explode(',', (string)$value);
                            $ids = array_values(array_filter(array_map(function($v){ $v=trim((string)$v); return ctype_digit($v) ? (int)$v : null; }, $vals), function($v){ return $v !== null; }));
                            if (empty($ids)) return;
                            \Log::debug('Skins filter parsed IDs (api)', ['raw'=>$value,'ids'=>$ids]);
                            $query->whereHas('attributes', function($attr) use ($ids) {
                                $attr->where('attribute_key', 'highlighted_skins')
                                     ->where(function($w) use ($ids) {
                                         foreach ($ids as $id) {
                                             $idStr = (string)$id;
                                             $w->orWhereRaw("REPLACE(attribute_value,' ', '') = ?", [$idStr])
                                               ->orWhereRaw("REPLACE(attribute_value,' ', '') LIKE ?", ["$idStr,%"]) 
                                               ->orWhereRaw("REPLACE(attribute_value,' ', '') LIKE ?", ["%,$idStr"]) 
                                               ->orWhereRaw("REPLACE(attribute_value,' ', '') LIKE ?", ["%,$idStr,%"]);
                                         }
                                     });
                            });
                        } catch (\Throwable $e) {
                            \Log::error('Skins filter error (filterAccounts): '.$e->getMessage(), [
                                'value' => $value,
                                'trace' => $e->getTraceAsString(),
                            ]);
                            throw $e;
                        }
                    }),
                    AllowedFilter::callback('win_rate', function ($query, $value) {
                        if ($value && strpos($value, '-') !== false) {
                            [$min, $max] = explode('-', $value);
                            $query->whereHas('attributes', function($q) use ($min, $max) {
                                $q->where('attribute_key', 'win_rate')
                                  ->whereBetween('attribute_value', [(float)$min, (float)$max]);
                            });
                        }
                    }),
                    AllowedFilter::callback('level', function ($query, $value) {
                        if ($value && strpos($value, '-') !== false) {
                            [$min, $max] = explode('-', $value);
                            $query->whereHas('attributes', function($q) use ($min, $max) {
                                $q->where('attribute_key', 'level')
                                  ->whereBetween('attribute_value', [(int)$min, (int)$max]);
                            });
                        }
                    }),
                    AllowedFilter::callback('verified', function ($query, $value) {
                        if ($value) {
                            $query->whereHas('seller', function($q) {
                                $q->where('verified', true);
                            });
                        }
                    }),
                    AllowedFilter::callback('instant', function ($query, $value) {
                        if ($value) {
                            // Placeholder for instant delivery
                        }
                    }),
                ])
                ->allowedSorts([
                    'price_dzd',
                    'created_at',
                    AllowedSort::field('title'),
                ])
                ->defaultSort('-created_at');

            $accounts = $query->get();

            return response()->json([
                'success' => true,
                'data' => $accounts,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        
        // Build query with Spatie Query Builder
        // Check if there are filter parameters in the request
        $hasFilters = $request->has('filter');
        
        $queryBuilder = QueryBuilder::for(AccountForSale::class)
            ->where('game_id', $game->id)
            ->where('status', 'available')
            ->with(['seller.user', 'attributes', 'images'])
            ->with(['seller' => function ($query) {
                $query->withCount(['orders' => function ($q) {
                    $q->where('status', 'completed');
                }]);
            }])
            ->allowedFilters([
                // Search filter - searches in title and description
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function($q) use ($value) {
                        $q->where('title', 'like', "%{$value}%")
                          ->orWhere('description', 'like', "%{$value}%");
                    });
                }),
                // Collection filter - searches in account attributes
                AllowedFilter::callback('collection', function ($query, $value) {
                    $query->whereHas('attributes', function($q) use ($value) {
                        $q->where('attribute_key', 'collection_tier')
                          ->where('attribute_value', '=', $value);
                    });
                }),
                // Platform filter - searches in account attributes
                AllowedFilter::callback('platform', function ($query, $value) {
                    $query->whereHas('attributes', function($q) use ($value) {
                        $q->where('attribute_key', 'platform');
                        if ($value === 'both') {
                            // For "both", check if the value contains both iOS and Android
                            $q->where('attribute_value', 'like', '%iOS%')
                              ->where('attribute_value', 'like', '%Android%');
                        } else {
                            $q->where('attribute_value', 'like', "%{$value}%");
                        }
                    });
                }),
                // Price range filter
                AllowedFilter::callback('price', function ($query, $value) {
                    if (strpos($value, '-') !== false) {
                        [$min, $max] = explode('-', $value);
                        $query->whereBetween('price_dzd', [
                            (int)$min, 
                            (int)$max
                        ]);
                    } elseif (strpos($value, '+') !== false) {
                        $min = (int)str_replace('+', '', $value);
                        $query->where('price_dzd', '>=', $min);
                    }
                }),
                // Skins filter - LIKE-based token matching (view path)
                AllowedFilter::callback('skins', function ($query, $value) {
                    try {
                        if ($value === null || $value === '') return;
                        $vals = is_array($value) ? $value : explode(',', (string)$value);
                        $ids = array_values(array_filter(array_map(function($v){ $v=trim((string)$v); return ctype_digit($v) ? (int)$v : null; }, $vals), function($v){ return $v !== null; }));
                        if (empty($ids)) return;
                        \Log::debug('Skins filter parsed IDs (show)', ['raw'=>$value,'ids'=>$ids]);
                        $query->whereHas('attributes', function($attr) use ($ids) {
                            $attr->where('attribute_key', 'highlighted_skins')
                                 ->where(function($w) use ($ids) {
                                     foreach ($ids as $id) {
                                         $idStr = (string)$id;
                                         $w->orWhereRaw("REPLACE(attribute_value,' ', '') = ?", [$idStr])
                                           ->orWhereRaw("REPLACE(attribute_value,' ', '') LIKE ?", ["$idStr,%"]) 
                                           ->orWhereRaw("REPLACE(attribute_value,' ', '') LIKE ?", ["%,$idStr"]) 
                                           ->orWhereRaw("REPLACE(attribute_value,' ', '') LIKE ?", ["%,$idStr,%"]);
                                     }
                                 });
                        });
                    } catch (\Throwable $e) {
                        \Log::error('Skins filter error (show): '.$e->getMessage(), [
                            'value' => $value,
                            'trace' => $e->getTraceAsString(),
                        ]);
                        throw $e;
                    }
                }),
                // Win Rate filter - searches in account attributes
                AllowedFilter::callback('win_rate', function ($query, $value) {
                    if ($value && strpos($value, '-') !== false) {
                        [$min, $max] = explode('-', $value);
                        $query->whereHas('attributes', function($q) use ($min, $max) {
                            $q->where('attribute_key', 'win_rate')
                              ->whereBetween('attribute_value', [(float)$min, (float)$max]);
                        });
                    }
                }),
                // Level filter - searches in account attributes
                AllowedFilter::callback('level', function ($query, $value) {
                    if ($value && strpos($value, '-') !== false) {
                        [$min, $max] = explode('-', $value);
                        $query->whereHas('attributes', function($q) use ($min, $max) {
                            $q->where('attribute_key', 'level')
                              ->whereBetween('attribute_value', [(int)$min, (int)$max]);
                        });
                    }
                }),
                // Rank filter - searches in account attributes
                AllowedFilter::callback('rank', function ($query, $value) {
                    $query->whereHas('attributes', function($q) use ($value) {
                        $q->where('attribute_key', 'rank')
                          ->where('attribute_value', 'like', "%{$value}%");
                    });
                }),
                // Verified seller filter
                AllowedFilter::callback('verified', function ($query, $value) {
                    if ($value) {
                        $query->whereHas('seller', function($q) {
                            $q->where('verified', true);
                        });
                    }
                }),
                // Instant delivery filter (placeholder - can be customized)
                AllowedFilter::callback('instant', function ($query, $value) {
                    if ($value) {
                        // Add your instant delivery logic here
                    }
                }),
            ])
            ->allowedSorts([
                'price_dzd',
                'created_at',
                AllowedSort::field('title'),
            ])
            ->defaultSort('-created_at');
        
        // Get accounts (QueryBuilder will automatically apply any filters from the request)
        $accounts = $queryBuilder->get();
        
        return view('games.show', [
            'game' => $game,
            'accounts' => $accounts
        ]);
    }
}
