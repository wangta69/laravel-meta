<?php
namespace Pondol\Meta\Services;

use Illuminate\Support\Facades\Route;

use Pondol\Meta\Models\Meta as mMeta;

class Meta
{
  static public function test () {
    return  [];
  }

  public $id;
  public $title = '';
  public $keywords = '';
  public $description = '';
  public $og_type = 'website';

  public function get() {

    $route_name = Route::currentRouteName(); 
    $route_params = Route::getCurrentRoute()->parameters;
    // print_r(Route::getCurrentRoute()->parameterNames);
    // // print_r(json_encode(Route::getCurrentRoute()->parameterNames));
    // print_r(json_encode(Route::getCurrentRoute()));
    // print_r(Route::getCurrentRoute()->parameters);

    $metas =  mMeta::firstOrCreate(['name' => $route_name, 'params'=>json_encode($route_params)]);
    return $metas;
    // print_r(json_encode(Route::getCurrentRoute()->parameterValues));

    // print_r($request->all());
  }
  public function set($meta) {
    $this->id = isset($meta->id) ? $meta->id:  null;
    $this->title = $meta->title;
    if ($this->id) {
      $this->ogImage($meta->id, $meta->title);
    }
    $this->keywords = $meta->keywords;
    $this->description = $meta->description;
    $this->updated_at = isset($meta->updated_at) ? $meta->updated_at : null;
    $this->created_at = isset($meta->created_at) ? $meta->created_at : null;
  }

  public function setTitle($title) {
    $this->title = $title;
    // $this->ogImage($title);
  }

  public function setOgImage($title=null) {
    $title = $title ? $title : $this->title;
    // $this->ogImage($title);
  }

  private function ogImage($id, $title) {
    $this->og_image = new \stdClass;
    $this->og_image->name = \App\Services\ViewerService::titleImage($id, $title);
    $this->og_image->alt = $title;
    $this->og_image->type = 'jpeg';
  }
}