<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Review
 */
class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'movie_id' => (int) $this->movie_id,
            'rating' => (int) $this->rating,
            'review' => $this->review,
            'created_at' => $this->created_at?->toISOString(),
            'movie' => new MovieResource($this->whenLoaded('movie')),
        ];
    }
}
