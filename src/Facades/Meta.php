<?php
namespace Pondol\Meta\Facades;

use Illuminate\Support\Facades\Facade;

class Meta extends Facade
{

  protected static $cached = false;
  protected static function getFacadeAccessor()
  {
    return 'meta';
  }
}
