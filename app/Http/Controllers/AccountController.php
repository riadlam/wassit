<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountForSale;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Return list of accounts, optionally filtered by game
    }

    public function show($slug, $id)
    {
        // Handle slug mapping: 'mobile-legends' -> 'mlbb'
        $gameSlug = $slug === 'mobile-legends' ? 'mlbb' : $slug;
        
        $game = \App\Models\Game::where('slug', $gameSlug)->firstOrFail();
        $account = AccountForSale::with(['game', 'seller.user', 'attributes', 'images'])
            ->where('id', $id)
            ->where('game_id', $game->id)
            ->where('status', 'available')
            ->firstOrFail();
        
        return view('accounts.show', [
            'game' => $game,
            'account' => $account
        ]);
    }
}
