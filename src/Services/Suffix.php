<?php
namespace Pondol\Meta\Services;

use Illuminate\Support\Facades\Route;

class Suffix
{


  private $meta;


  public function __construct($meta)
  {
    $this->meta = $meta;
  }

  public function __set($name, $value) {
    $this->meta->{$name} .= $value;
  }

}