<?php
namespace Pondol\Meta;

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
    // Register migrations
    $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    $this->loadViewsFrom(__DIR__.'/resources/views', 'pondol-meta');

    $this->commands([
      InstallCommand::class
    ]);
  }


}
