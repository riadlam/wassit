<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\MlbbSkin;

class MlbbSkinSeeder extends Seeder
{
    public function run(): void
    {
        $path = 'mlbbskins.json';
        if (!Storage::disk('public')->exists($path)) {
            $this->command?->warn('public/mlbbskins.json not found; skipping MlbbSkinSeeder.');
            return;
        }

        $json = Storage::disk('public')->get($path);
        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['categories'])) {
            $this->command?->warn('Invalid mlbbskins.json structure; skipping MlbbSkinSeeder.');
            return;
        }

        $records = [];
        foreach ($data['categories'] as $category) {
            $role = trim((string)($category['name'] ?? ''));
            $roleSlug = Str::slug($role);
            foreach (($category['heroes'] ?? []) as $heroEntry) {
                $hero = trim((string)($heroEntry['hero'] ?? ''));
                $heroSlug = Str::slug($hero);
                foreach (($heroEntry['skins'] ?? []) as $skinName) {
                    $skin = trim((string)$skinName);
                    if ($skin === '' || $hero === '' || $role === '') continue;
                    $skinSlug = Str::slug($skin);

                    $records[] = [
                        'role' => $role,
                        'hero' => $hero,
                        'skin' => $skin,
                        'role_slug' => $roleSlug,
                        'hero_slug' => $heroSlug,
                        'skin_slug' => $skinSlug,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Upsert to avoid duplicates if re-seeding
        if (!empty($records)) {
            MlbbSkin::upsert($records, ['hero_slug', 'skin_slug'], ['role', 'role_slug', 'updated_at']);
        }
    }
}
