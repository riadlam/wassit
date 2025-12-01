<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\AccountForSale;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

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

    public function filterAccounts(Request $request, $slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        
        // Build query with Spatie Query Builder
        $accounts = QueryBuilder::for(AccountForSale::class)
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
                // Rank filter - searches in account attributes
                AllowedFilter::callback('rank', function ($query, $value) {
                    $query->whereHas('attributes', function($q) use ($value) {
                        $q->where('attribute_key', 'rank')
                          ->where('attribute_value', 'like', "%{$value}%");
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
                            (int)$min * 100, 
                            (int)$max * 100
                        ]);
                    } elseif (strpos($value, '+') !== false) {
                        $min = (int)str_replace('+', '', $value) * 100;
                        $query->where('price_dzd', '>=', $min);
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
            ->defaultSort('-created_at')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    public function show(Request $request, $slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        
        // Build query with Spatie Query Builder
        $accounts = QueryBuilder::for(AccountForSale::class)
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
                            (int)$min * 100, 
                            (int)$max * 100
                        ]);
                    } elseif (strpos($value, '+') !== false) {
                        $min = (int)str_replace('+', '', $value) * 100;
                        $query->where('price_dzd', '>=', $min);
                    }
                }),
                // Skins filter - searches in account attributes
                AllowedFilter::callback('skins', function ($query, $value) {
                    if ($value) {
                        $skinFilters = explode(',', $value);
                        $query->where(function($q) use ($skinFilters) {
                            foreach ($skinFilters as $skinFilter) {
                                // Format: role-hero-skin (e.g., assassin-ling-aoen-of-twilight)
                                $parts = explode('-', $skinFilter);
                                if (count($parts) >= 3) {
                                    // Reconstruct skin name (may contain multiple hyphens)
                                    $role = $parts[0];
                                    $hero = $parts[1];
                                    $skinName = implode('-', array_slice($parts, 2));
                                    // Also try with spaces for matching
                                    $skinNameWithSpaces = str_replace('-', ' ', $skinName);
                                    $heroWithSpaces = str_replace('-', ' ', $hero);
                                    
                                    $q->orWhereHas('attributes', function($attrQuery) use ($hero, $heroWithSpaces, $skinName, $skinNameWithSpaces) {
                                        // Search for skin name in attribute values (skins might be stored in various formats)
                                        $attrQuery->where(function($subQuery) use ($hero, $heroWithSpaces, $skinName, $skinNameWithSpaces) {
                                            // Try matching skin name directly
                                            $subQuery->where('attribute_value', 'like', "%{$skinName}%")
                                                    ->orWhere('attribute_value', 'like', "%{$skinNameWithSpaces}%")
                                                    // Also try matching hero name + skin name
                                                    ->orWhere(function($q2) use ($hero, $heroWithSpaces, $skinName, $skinNameWithSpaces) {
                                                        $q2->where('attribute_key', 'like', "%{$hero}%")
                                                           ->where('attribute_value', 'like', "%{$skinName}%");
                                                    })
                                                    ->orWhere(function($q3) use ($hero, $heroWithSpaces, $skinName, $skinNameWithSpaces) {
                                                        $q3->where('attribute_key', 'like', "%{$heroWithSpaces}%")
                                                           ->where('attribute_value', 'like', "%{$skinNameWithSpaces}%");
                                                    });
                                        });
                                    });
                                }
                            }
                        });
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
            ->defaultSort('-created_at')
            ->get();
        
        return view('games.show', [
            'game' => $game,
            'accounts' => $accounts
        ]);
    }
}
