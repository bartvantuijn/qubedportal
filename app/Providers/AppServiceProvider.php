<?php

namespace App\Providers;

use Carbon\Carbon;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
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

        // Register custom colors
        $this->app->singleton('colors.primary', function () {
            return '#262626';
        });
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

        // Register favicon render hook
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): View => view('filament.admin.favicon'),
        );
    }
}
