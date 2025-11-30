<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon_url',
    ];

    /**
     * Get the static attributes for this game.
     */
    public function staticAttributes(): HasMany
    {
        return $this->hasMany(GameStaticAttribute::class, 'game_id');
    }

    /**
     * Get the accounts for sale for this game.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(AccountForSale::class, 'game_id');
    }
}
