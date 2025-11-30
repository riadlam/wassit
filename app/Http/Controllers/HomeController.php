<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Get MLBB first if it exists
        $mlbbGame = Game::where('slug', 'mlbb')
            ->withCount(['accounts' => function($query) {
                $query->where('status', 'available');
            }])
            ->first();
        
        // Get 7 more random games (excluding MLBB)
        $otherGames = Game::where('slug', '!=', 'mlbb')
            ->withCount(['accounts' => function($query) {
                $query->where('status', 'available');
            }])
            ->inRandomOrder()
            ->limit(7)
            ->get();
        
        // Combine: MLBB first, then 7 random games, total 8
        $games = collect();
        if ($mlbbGame) {
            $games->push($mlbbGame);
        }
        $games = $games->merge($otherGames)->take(8);
        
        // Dummy slider data
        $slides = [
            [
                'title' => 'Premium Gaming Accounts',
                'subtitle' => 'Buy and sell verified gaming accounts safely',
                'button_text' => 'Browse Accounts',
                'link' => '#games',
                'image' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200&q=80'
            ],
            [
                'title' => 'Trusted Marketplace',
                'subtitle' => 'Secure transactions with verified sellers',
                'button_text' => 'Get Started',
                'link' => '#games',
                'image' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=1200&q=80'
            ],
            [
                'title' => 'Best Prices Guaranteed',
                'subtitle' => 'Find the best deals on premium accounts',
                'button_text' => 'Shop Now',
                'link' => '#games',
                'image' => 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?w=1200&q=80'
            ],
        ];
        
        return view('home', [
            'games' => $games,
            'slides' => $slides
        ]);
    }
}
