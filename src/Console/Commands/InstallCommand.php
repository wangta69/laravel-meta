<?php

namespace Pondol\Meta\Console\Commands;

use Illuminate\Console\Command;
// use Illuminate\Filesystem\Filesystem;
// use Illuminate\Support\Str;
// use Symfony\Component\Process\PhpExecutableFinder;
// use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
  // use InstallsBladeStack;

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'pondol:install-meta {type=full}'; // full | only

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = "Install Pondol's Meta Manager";


  public function __construct()
  {
    parent::__construct();
  }

  public function handle()
  {
    $type = $this->argument('type');
    return $this->installLaravelMeta($type);
  }


  private function installLaravelMeta($type)
  {

    \Artisan::call('vendor:publish',  [
      '--force'=> true,
      '--provider' => 'Pondol\Meta\MetaServiceProvider'
    ]);
    \Artisan::call('migrate');
    $this->info("The pondol's laravel metagtag manager system installed successfully.");
    
  }


}
