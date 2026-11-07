<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Watchlist\StoreWatchlistRequest;
use App\Http\Resources\WatchlistResource;
use App\Models\Movie;
use App\Models\Watchlist;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WatchlistController extends Controller
{
    /**
     * Display the authenticated user's watchlist.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $items = Watchlist::query()
            ->with('movie')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return WatchlistResource::collection($items);
    }

    /**
     * Add a movie to the authenticated user's watchlist.
     */
    public function store(StoreWatchlistRequest $request): JsonResponse
    {
        $movieId = (int) $request->validated('movieId');

        $movie = Movie::find($movieId);
        if (! $movie) {
            abort(404, 'Film tidak ditemukan.');
        }

        try {
            $watchlist = Watchlist::create([
                'user_id' => $request->user()->id,
                'movie_id' => $movie->id,
            ]);
        } catch (UniqueConstraintViolationException) {
            return response()->json([
                'message' => 'Film sudah ada di dalam watchlist Anda.',
            ], 409);
        }

        $watchlist->setRelation('movie', $movie);

        return response()->json([
            'message' => 'Film berhasil ditambahkan ke watchlist.',
            'data' => new WatchlistResource($watchlist),
        ], 201);
    }

    /**
     * Remove a movie from the authenticated user's watchlist.
     */
    public function destroy(Request $request, int $movieId): JsonResponse
    {
        $deleted = Watchlist::query()
            ->where('user_id', $request->user()->id)
            ->where('movie_id', $movieId)
            ->delete();

        if (! $deleted) {
            abort(404, 'Film tidak ditemukan di dalam watchlist Anda.');
        }

        return response()->json([
            'message' => 'Film berhasil dihapus dari watchlist.',
        ]);
    }
}
