<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\User;
use Database\Seeders\MovieSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class WatchlistApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Log::swap(new LogManager($this->app));
        config(['logging.default' => 'null']);
    }

    public function test_protected_endpoints_return_401_without_token_even_without_accept_header(): void
    {
        $watchlistResponse = $this->get('/api/watchlist');
        $watchlistResponse->assertStatus(401)
            ->assertJsonStructure(['message']);

        $watchedResponse = $this->get('/api/watched');
        $watchedResponse->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    public function test_adding_duplicate_movie_to_watchlist_returns_409_conflict(): void
    {
        $this->seed(MovieSeeder::class);
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);
        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('token');

        $firstAdd = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/watchlist', [
                'movieId' => 1,
            ]);
        $firstAdd->assertStatus(201)
            ->assertJsonPath('data.movie_id', 1);

        $duplicateAdd = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/watchlist', [
                'movieId' => 1,
            ]);
        $duplicateAdd->assertStatus(409)
            ->assertJsonStructure(['message']);
    }

    public function test_submitting_watched_review_with_rating_outside_1_to_5_returns_422(): void
    {
        $this->seed(MovieSeeder::class);
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $token = auth('api')->login($user);

        $highRatingResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/watched', [
                'movieId' => 1,
                'rating' => 9,
                'review' => 'Rating terlalu tinggi di luar skala 1-5.',
            ]);
        $highRatingResponse->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['rating']]);

        $lowRatingResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/watched', [
                'movieId' => 1,
                'rating' => 0,
                'review' => 'Rating terlalu rendah di luar skala 1-5.',
            ]);
        $lowRatingResponse->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['rating']]);
    }

    public function test_user_cannot_access_or_delete_another_users_watchlist(): void
    {
        $this->seed(MovieSeeder::class);
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $tokenA = auth('api')->login($userA);
        $this->withHeader('Authorization', "Bearer {$tokenA}")
            ->postJson('/api/watchlist', ['movieId' => 1])
            ->assertStatus(201);

        $tokenB = auth('api')->login($userB);
        $this->withHeader('Authorization', "Bearer {$tokenB}")
            ->getJson('/api/watchlist')
            ->assertStatus(200)
            ->assertJsonCount(0);

        $this->withHeader('Authorization', "Bearer {$tokenB}")
            ->deleteJson('/api/watchlist/1')
            ->assertStatus(404);
    }

    public function test_gemini_recommendation_validates_movie_id_against_database_catalog(): void
    {
        $this->seed(MovieSeeder::class);
        config(['services.gemini.api_key' => 'fake-gemini-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => json_encode(['movie_id' => 999, 'reasoning' => 'ID di luar katalog.'])],
                                ],
                            ],
                        ],
                    ],
                ], 200)
                ->push([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => json_encode(['movie_id' => 14, 'reasoning' => 'Memento sangat cocok karena bertema misteri.'])],
                                ],
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $response = $this->postJson('/api/movies/recommend', [
            'prompt' => 'Film misteri tentang ingatan',
        ]);

        $response->assertStatus(200)
            ->assertExactJson([
                'movie_id' => 14,
                'reasoning' => 'Memento sangat cocok karena bertema misteri.',
            ]);
        $this->assertTrue(Movie::whereKey(14)->exists());
    }
}
