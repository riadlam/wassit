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
        static::saving(function (AccountImage $image): void {
            $path = (string) $image->url;
            if ($path === '' || str_ends_with(strtolower($path), '.webp')) {
                return;
            }

            $absolute = public_path('storage/'.ltrim(str_replace('\\', '/', $path), '/'));
            if (! is_file($absolute)) {
                return;
            }

            try {
                /** @var \App\Services\WebpImageService $webp */
                $webp = app(\App\Services\WebpImageService::class);
                if (! $webp->supportsWebp()) {
                    return;
                }

                $directory = trim(str_replace('\\', '/', dirname($path)), '/.');
                $filename = time().'_'.uniqid().'.webp';
                $relative = ($directory !== '' ? $directory.'/' : '').$filename;
                $destination = public_path('storage/'.$relative);
                $destDir = dirname($destination);
                if (! is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }

                $webp->convertPathToWebp($absolute, $destination, $image->is_cover ? 1600 : 1600);
                if (is_file($destination)) {
                    @unlink($absolute);
                    $image->url = $relative;
                }
            } catch (\Throwable $e) {
                \Log::warning('AccountImage WebP conversion skipped', [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        });

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
