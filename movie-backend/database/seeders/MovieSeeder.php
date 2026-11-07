<?php

namespace Database\Seeders;

use App\Models\Movie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MovieSeeder extends Seeder
{
    /**
     * Seed the movies table from JSON data.
     */
    public function run(): void
    {
        $path = database_path('data/movies.json');
        $movies = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        foreach ($movies as $movie) {
            Movie::updateOrCreate(
                ['id' => $movie['id']],
                [
                    'title' => $movie['title'],
                    'year' => $movie['year'],
                    'director' => $movie['director'],
                    'genres' => $movie['genres'],
                    'rating' => $movie['rating'],
                    'duration' => $movie['duration'],
                    'synopsis' => $movie['synopsis'],
                    'poster_url' => $movie['poster_url'],
                    'cast_members' => $movie['cast'],
                ],
            );
        }
    }
}
