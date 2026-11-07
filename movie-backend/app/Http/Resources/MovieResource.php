<?php

namespace App\Http\Resources;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Movie
 */
class MovieResource extends JsonResource
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
            'title' => $this->title,
            'year' => (int) $this->year,
            'director' => $this->director,
            'genres' => $this->genres ?? [],
            'rating' => (float) $this->rating,
            'duration' => $this->duration,
            'synopsis' => $this->synopsis,
            'poster_url' => $this->poster_url,
            'cast' => $this->cast_members ?? [],
        ];
    }
}
