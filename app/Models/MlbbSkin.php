<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MlbbSkin extends Model
{
    use HasFactory;

    protected $fillable = [
        'role', 'hero', 'skin', 'role_slug', 'hero_slug', 'skin_slug'
    ];
}
