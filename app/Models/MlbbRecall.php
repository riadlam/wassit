<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MlbbRecall extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'group',
        'description',
        'thumbnail_url',
        'image_url',
    ];
}
