<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AccountImage extends Model
{
    protected $fillable = [
        'account_id',
        'url',
        'is_cover',
    ];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (AccountImage $image): void {
            if (! $image->wasChanged('url')) {
                return;
            }

            static::deleteFileIfUnused((string) $image->getOriginal('url'), $image->id);
        });

        static::deleted(function (AccountImage $image): void {
            static::deleteFileIfUnused((string) $image->url, $image->id);
        });
    }

    private static function deleteFileIfUnused(string $path, int $ignoredId): void
    {
        if ($path === '') {
            return;
        }

        $isStillReferenced = static::query()
            ->where('url', $path)
            ->where('id', '!=', $ignoredId)
            ->exists();

        if (! $isStillReferenced) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Get the account that owns this image.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountForSale::class, 'account_id');
    }
}
