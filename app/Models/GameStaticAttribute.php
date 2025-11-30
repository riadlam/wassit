<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameStaticAttribute extends Model
{
    protected $fillable = [
        'game_id',
        'attribute_key',
        'attribute_label',
        'type',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    /**
     * Get the game that owns this attribute.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
}
