<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Movie;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WatchedController extends Controller
{
    /**
     * Display the authenticated user's watched movies and reviews.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $reviews = Review::query()
            ->with('movie')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return ReviewResource::collection($reviews);
    }

    /**
     * Log or update a movie review for the authenticated user.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $movieId = (int) $validated['movieId'];

        $movie = Movie::find($movieId);
        if (! $movie) {
            abort(404, 'Film tidak ditemukan.');
        }

        $review = Review::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'movie_id' => $movie->id,
            ],
            [
                'rating' => (int) $validated['rating'],
                'review' => $validated['review'],
            ],
        );

        $review->setRelation('movie', $movie);

        $statusCode = $review->wasRecentlyCreated ? 201 : 200;
        $message = $review->wasRecentlyCreated
            ? 'Catatan tontonan dan ulasan berhasil disimpan.'
            : 'Catatan tontonan dan ulasan berhasil diperbarui.';

        return response()->json([
            'message' => $message,
            'data' => new ReviewResource($review),
        ], $statusCode);
    }
}
