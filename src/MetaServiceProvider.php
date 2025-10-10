<?php
namespace Pondol\Meta;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

use Pondol\Meta\Console\Commands\InstallCommand;
use Pondol\Meta\Services\Meta;

class MetaServiceProvider extends ServiceProvider {


  /**
   * Where the route file lives, both inside the package and in the app (if overwritten).
   *
   * @var string
   */

	/**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    $this->app->singleton('meta', function () {
      return new Meta();
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
    if (!config()->has('pondol-meta')) {
      $this->publishes([
        __DIR__ . '/config/pondol-meta.php' => config_path('pondol-meta.php'),
      ], 'config');  
    } 
      
    $this->mergeConfigFrom(
      __DIR__ . '/config/pondol-meta.php',
      'pondol-meta'
    );


    // Register migrations
    $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    $this->loadViewsFrom(__DIR__.'/resources/views', 'pondol-meta');

    $this->commands([
      InstallCommand::class
    ]);

    // LOAD THE VIEWS
    // - first the published views (in case they have any changes)
    $this->publishes([
      // copy resource 파일
      __DIR__.'/resources/assets/' => public_path('pondol/meta'),
      // controllers;
      // __DIR__.'/Http/Controllers/Bbs/' => app_path('Http/Controllers/Bbs')
    ]);

    $this->loadMetaRoutes();
  }

  private function loadMetaRoutes()
  {
    $config = config('pondol-meta.route_sitemap');
    Route::prefix($config['prefix'])
      ->as($config['as'])
      ->middleware($config['middleware'])
      ->namespace('Pondol\Meta\Http\Controllers')
      ->group(__DIR__ . '/routes/sitemap.php');
    
    $config = config('pondol-meta.route_meta_admin');
    Route::prefix($config['prefix'])
      ->as($config['as'])
      ->middleware($config['middleware'])
      ->namespace('Pondol\Meta\Http\Controllers\Admin')
      ->group(__DIR__ . '/routes/meta-admin.php');
  }
}
