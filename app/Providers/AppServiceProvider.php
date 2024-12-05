<?php

namespace App\Providers;

use Carbon\Carbon;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $loader = AliasLoader::getInstance();

        $loader->alias('Carbon', Carbon::class);
        $loader->alias('FilamentAsset', FilamentAsset::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        // Force HTTPS scheme
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Register custom assets
        FilamentAsset::register([
            Js::make('main', Vite::asset('resources/js/main.js')),
        ]);

    }
}
