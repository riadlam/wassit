<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MlbbSkin extends Model
{
    use HasFactory;

    protected $fillable = [
        'role',
        'hero',
        'skin',
        'role_slug',
        'hero_slug',
        'skin_slug',
        'sort_order',
        'rarity',
        'painted',
        'image_path',
        'thumbnail_path',
        'source_image_url',
        'tags',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'painted' => 'boolean',
            'tags' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function imageUrl(): ?string
    {
        $path = $this->image_path ?: $this->thumbnail_path;

        return $path ? asset('storage/'.$path) : null;
    }

    public function thumbnailUrl(): ?string
    {
        $path = $this->thumbnail_path ?: $this->image_path;

        return $path ? asset('storage/'.$path) : null;
    }
}
