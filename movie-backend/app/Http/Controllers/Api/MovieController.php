<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MovieController extends Controller
{
    /**
     * Display a listing of movies with optional genre, search, and random filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Movie::query();

        if ($request->filled('genre')) {
            $genre = trim((string) $request->query('genre'));
            if (strtoupper($genre) !== 'ALL') {
                $query->whereJsonContains('genres', $genre);
            }
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function (Builder $q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('director', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('random')) {
            $query->inRandomOrder();
        } else {
            $query->orderBy('id');
        }

        return MovieResource::collection($query->get());
    }

    /**
     * Display the specified movie.
     */
    public function show(int $id): MovieResource
    {
        $movie = Movie::find($id);

        if (! $movie) {
            abort(404, 'Film tidak ditemukan.');
        }

        return new MovieResource($movie);
    }
}
