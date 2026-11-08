<?php

namespace App\Providers;

use Database\Seeders\MovieSeeder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $dbUrl = config('database.connections.pgsql.url') ?: env('DB_URL');

        if (is_string($dbUrl) && str_contains($dbUrl, '.neon.tech')) {
            $parsed = parse_url($dbUrl);
            $host = $parsed['host'] ?? '';
            $firstLabel = explode('.', $host)[0];
            $endpointId = preg_replace('/-pooler$/', '', $firstLabel);

            config([
                'database.connections.pgsql.url' => null,
                'database.connections.pgsql.host' => $host,
                'database.connections.pgsql.port' => $parsed['port'] ?? 5432,
                'database.connections.pgsql.database' => isset($parsed['path']) ? ltrim($parsed['path'], '/') : 'neondb',
                'database.connections.pgsql.username' => $parsed['user'] ?? '',
                'database.connections.pgsql.password' => $parsed['pass'] ?? '',
                'database.connections.pgsql.sslmode' => "require;options='endpoint={$endpointId}'",
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        if (isset($_ENV['VERCEL']) || getenv('VERCEL')) {
            try {
                if (! Schema::hasTable('movies')) {
                    Artisan::call('migrate', ['--force' => true]);
                    Artisan::call('db:seed', ['--class' => MovieSeeder::class, '--force' => true]);
                }
            } catch (Throwable) {
                // Ignore during boot so the actual route handler can report any DB configuration issue
            }
        }
    }
}
