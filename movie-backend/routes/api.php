<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\WatchedController;
use App\Http\Controllers\Api\WatchlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{id}', [MovieController::class, 'show'])->whereNumber('id');
Route::post('/movies/recommend', [MovieController::class, 'recommend'])->middleware('throttle:10,1');

Route::middleware('auth:api')->group(function (): void {
    Route::get('/watchlist', [WatchlistController::class, 'index']);
    Route::post('/watchlist', [WatchlistController::class, 'store']);
    Route::delete('/watchlist/{movieId}', [WatchlistController::class, 'destroy'])->whereNumber('movieId');

    Route::get('/watched', [WatchedController::class, 'index']);
    Route::post('/watched', [WatchedController::class, 'store']);
});
