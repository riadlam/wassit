<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MlbbSkinTag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image_path',
        'source_url',
    ];

    public function publicUrl(): ?string
    {
        if (! $this->image_path || ! is_file(storage_path('app/public/'.$this->image_path))) {
            return null;
        }

        return '/storage/'.ltrim($this->image_path, '/');
    }
}
