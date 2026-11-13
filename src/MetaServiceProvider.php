<?php

namespace Pondol\Meta;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Pondol\Meta\Console\Commands\InstallCommand;
use Pondol\Meta\Services\Meta;

class MetaServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('meta', function () {
            return new Meta;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config file and merge
        if (! config()->has('pondol-meta')) {
            $this->publishes([
                __DIR__.'/config/pondol-meta.php' => config_path('pondol-meta.php'),
            ], 'config');
        }

        $this->mergeConfigFrom(
            __DIR__.'/config/pondol-meta.php',
            'pondol-meta'
        );

        // Register migrations & views
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'pondol-meta');

        $this->commands([
            InstallCommand::class,
        ]);

        // Publish assets
        $this->publishes([
            __DIR__.'/resources/assets/' => public_path('pondol/meta'),
        ], 'pondol-meta-assets');

        // Publish skins -> resources/views/vendor/pondol-meta/skins
        $this->publishes([
            __DIR__.'/resources/views/skins' => resource_path('views/vendor/pondol-meta/skins'),
        ], 'pondol-meta-skins');

        $this->loadMetaRoutes();
    }

    private function loadMetaRoutes()
    {
        $config = config('pondol-meta.route_sitemap');
        Route::prefix($config['prefix'])
            ->as($config['as'])
            ->middleware($config['middleware'])
            ->namespace('Pondol\Meta\Http\Controllers')
            ->group(__DIR__.'/routes/sitemap.php');

        $config = config('pondol-meta.route_meta_admin');
        Route::prefix($config['prefix'])
            ->as($config['as'])
            ->middleware($config['middleware'])
            ->namespace('Pondol\Meta\Http\Controllers\Admin')
            ->group(__DIR__.'/routes/meta-admin.php');
    }
}
