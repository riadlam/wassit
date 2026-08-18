<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MlbbEmote extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'group',
        'description',
        'heroes',
        'thumbnail_url',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'heroes' => 'array',
        ];
    }
}
