<?php
namespace Pondol\Meta\Services;

use Illuminate\Support\Facades\Route;

use Pondol\Meta\Models\Meta as mMeta;

class Meta
{


  public $id;
  public $title = '';
  public $keywords = '';
  public $description = '';
  public $og_type = 'website';
  public $og;
  
  public function __construct()
  {
    $this->og = new \stdClass;
  }
  
  public function set($route_name, $route_params) {
    $meta = mMeta::firstOrCreate(['name' => $route_name, 'params'=>json_encode($route_params)]);
    $this->id = $meta->id;
    $this->title = $meta->title;
    $this->keywords = $meta->keywords;
    $this->description = $meta->description;
    $this->og->image = $meta->image;
    return $this;
  }
  public function get() {
    $route_name = Route::currentRouteName(); 

    $route_params = [];
    foreach(Route::getCurrentRoute()->parameterNames as $p) {
      $route_params[$p] = Route::getCurrentRoute()->originalParameter($p);
    }
    return $this->set($route_name, $route_params);
  }
  
  

  public function title($title) {
    $this->title = $title ?? $this->title;
    return $this;
  }

  public function keywords($keywords) {
    $this->keywords = $keywords ?? $this->keywords;
    return $this;
  }

  // 2차 배열용
  public function extractKeywordsFromArray($arr, $key) {
    $keywords = [];
    foreach($arr as $val) {
      foreach($val as $k=>$v) {
        if($k == $key) {
          array_push($keywords, $v);
        }
      }
    }
    $this->keywords = implode(',', $keywords);
    return $this;
  }

  public function description($description) {
    $this->description = $description ?? $this->description;
    return $this;
  }

  public function update() {
    mMeta::where('id', $this->id)->update([
      'title'=>$this->title,
      'keywords'=>$this->keywords,
      'description'=>$this->description,
      'image'=>$this->og->image
    ]);
  }

  public function image($url) {
    $this->og->image = $url;
    return $this;
  }

  /** @deprecated */
  public function setTitle($title) {
    $this->title = $title;
    // $this->ogImage($title);
  }

/*
  public function image($title=null) {
    $title = $title ? $title : $this->title;
    $this->og = new \stdClass;
    // $this->og->image = \App\Services\ViewerService::titleImage($id, $title);
    $this->og->alt = $title;
    $this->og->type = 'jpeg';
    // $this->ogImage($title);
  }
  */



  /**
   * og용 이미지 제작
   */
  public function create_og_image($title) {
    $image_path = public_path()."/title-images";

    if (file_exists($image_path.'/'.$this->id.'.jpg')) {
      return '/title-images/'.$this->id.'.jpg';
    }

    $img = imagecreatefromjpeg(public_path()."/assets/images/title.jpg");//replace with your image 

    $fontFile = public_path()."/assets/fonts/NanumGothic.ttf";//replace with your font
    $fontSize = 24;
    $fontColor = imagecolorallocate($img, 255, 255, 255);
    $black = imagecolorallocate($img, 255, 255, 255);
    $angle = 0;
  
    $iWidth = imagesx($img);
    $iHeight = imagesy($img);
  
    $tSize = imagettfbbox($fontSize, $angle, $fontFile, $title);
    $tWidth = max([$tSize[2], $tSize[4]]) - min([$tSize[0], $tSize[6]]);
    $tHeight = max([$tSize[5], $tSize[7]]) - min([$tSize[1], $tSize[3]]);
    // text is placed in center you can change it by changing $centerX, $centerY values
    $centerX = CEIL(($iWidth - $tWidth) / 2);
    $centerX = $centerX<0 ? 0 : $centerX;
    $centerY = CEIL(($iHeight - $tHeight) / 2);
    $centerY = $centerY<0 ? 0 : $centerY;

    // print_r($centerX);
    // print_r($centerX);
    imagettftext($img, $fontSize, $angle, $centerX, $centerY, $black, $fontFile, $title);
    imagejpeg($img, $image_path.'/'.$id.'.jpg');//save image
    imagedestroy($img);
    return '/title-images/'.$id.'.jpg';
  }

  /** @deprecated */
  // private function ogImage($id, $title) {
  //   $this->og_image = new \stdClass;
  //   $this->og_image->name = \App\Services\ViewerService::titleImage($id, $title);
  //   $this->og_image->alt = $title;
  //   $this->og_image->type = 'jpeg';
  // }
}