<?php

namespace App\Http\Resources;

use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Watchlist
 */
class WatchlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'movie_id' => (int) $this->movie_id,
            'movie' => new MovieResource($this->whenLoaded('movie')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
