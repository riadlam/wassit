<?php

namespace App\Http\Controllers;

use App\Models\MlbbSkin;
use Illuminate\Http\Request;

class MlbbSkinController extends Controller
{
    public function index(Request $request)
    {
        // Return structure similar to existing JSON for drop-in replacement
        $all = MlbbSkin::query()
            ->orderBy('role')
            ->orderBy('hero')
            ->orderBy('skin')
            ->get(['id', 'role', 'hero', 'skin']);

        $grouped = [];
        foreach ($all as $row) {
            $role = $row->role;
            $hero = $row->hero;
            if (!isset($grouped[$role])) {
                $grouped[$role] = [];
            }
            if (!isset($grouped[$role][$hero])) {
                $grouped[$role][$hero] = ['hero' => $hero, 'skins' => []];
            }
            $grouped[$role][$hero]['skins'][] = [
                'id' => (int)$row->id,
                'name' => $row->skin,
            ];
        }

        $categories = [];
        foreach ($grouped as $role => $heroes) {
            $heroesArr = [];
            foreach ($heroes as $heroData) {
                // Convert to old format but keep ids alongside
                $heroesArr[] = [
                    'hero' => $heroData['hero'],
                    'skins' => array_map(fn($s) => $s['name'], $heroData['skins']),
                    'skins_with_ids' => $heroData['skins'],
                ];
            }
            $categories[] = [
                'name' => $role,
                'heroes' => $heroesArr,
            ];
        }

        return response()->json(['categories' => $categories]);
    }
}
