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
        if (! $this->image_path) {
            return null;
        }

        return asset('storage/'.$this->image_path);
    }
}
