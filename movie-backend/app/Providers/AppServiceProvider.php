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
        //
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
