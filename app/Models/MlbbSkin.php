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

    public function hasPublicImage(): bool
    {
        $path = $this->image_path ?: $this->thumbnail_path;
        if (! $path) {
            return false;
        }

        return is_file(storage_path('app/public/'.$path));
    }

    public function imageUrl(): ?string
    {
        if ($this->hasPublicImage()) {
            return '/storage/'.ltrim((string) ($this->image_path ?: $this->thumbnail_path), '/');
        }

        $remote = trim((string) ($this->source_image_url ?? ''));

        return $remote !== '' ? $remote : null;
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->hasPublicImage()) {
            return '/storage/'.ltrim((string) ($this->thumbnail_path ?: $this->image_path), '/');
        }

        $remote = trim((string) ($this->source_image_url ?? ''));

        return $remote !== '' ? $remote : null;
    }
}
