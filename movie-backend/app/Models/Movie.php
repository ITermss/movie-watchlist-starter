<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'genres' => 'array',
            'rating' => 'float',
            'cast_members' => 'array',
        ];
    }

    /**
     * Get the watchlist entries for the movie.
     */
    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    /**
     * Get the reviews for the movie.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
