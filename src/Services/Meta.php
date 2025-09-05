<?php
namespace Pondol\Meta\Services;

use Illuminate\Support\Facades\Route;

use Pondol\Meta\Models\Meta as mMeta;
use Pondol\Meta\Services\Image;
class Meta
{


  public $id;
  public $title = '';
  public $keywords = '';
  public $description = '';
  public $path;
  public $created_at;
  public $updated_at;
  // public $og_type = 'article'; // website
  public $og;
  public $twitter;

  // 검색 엔진이 더 이상 사용하지 않음
  // public $revisitAfter;
  // public $coverage;
  // public $distribution;
  // public $rating;


  public $robots = 'index,follow';

  public function __construct()
  {
    $this->og = new \stdClass;
    $this->og->type = 'article'; //'website'
    $this->og->locale = 'ko_KR';
    $this->og->site_name = config('app.name', 'OnStory');

    $this->twitter = new \stdClass;
    $this->twitter->card = 'summary_large_image'; // summary
    //  아래 두개는 twitter 계정이 있을 경우에만 사용
    // $this->twitter->site = config('app.name', 'OnStory');
    // $this->twitter->creator = '';

    $this->robots = config('pondol-meta.robots');
    // $this->revisitAfter = config('pondol-meta.revisit-after');
    // $this->coverage = config('pondol-meta.coverage');
    // $this->distribution = config('pondol-meta.distribution');
    // $this->rating = config('pondol-meta.rating');
  }
  
  /**
   * 게시물 등록이나 수정시 사용
   */
  public function set($route_name, $route_params) {

    $meta = mMeta::firstOrCreate(['name' => $route_name, 'params'=>json_encode($route_params)]);
    $this->id = $meta->id;
    $this->title = $meta->title;
    $this->keywords = $meta->keywords;
    $this->description = $meta->description;
    
    $this->created_at = $meta->created_at;
    $this->updated_at = $meta->updated_at;
    $this->og->image = $meta->image;

    if(!$meta->path && $route_name) {
      try {
        $meta->path = str_replace(config('app.url'), '', route($route_name, $route_params));
      
        $meta->save();
      }
      catch ( \Exception $e )
      {
        \Log::debug($e->getMessage());
      }
    }

    $this->path = $meta->path;
    return $this;
  }
  
  public function get() {
   
    $route_name = Route::currentRouteName(); 
    // $type='route';
    if(!$route_name) {
      $route_name = request()->path();
      // $type='path';
    }

    $route_params = [];
    foreach(Route::getCurrentRoute()->parameterNames as $p) {
      $route_params[$p] = Route::getCurrentRoute()->originalParameter($p);
    }
    return $this->set($route_name, $route_params);
  }
  /** @deprecated */
  public function title($title) {
    $this->title = $title ?? $this->title;
    return $this;
  }
/** @deprecated */
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
/** @deprecated */
  public function description($description) {
    $this->description = $description ?? $this->description;
    return $this;
  }

  public function image($path) {
    $this->og->image = $path;
    return $this;
  }
/** @deprecated */
  public function path($path) {
    $this->path = $path ?? $this->path;
    return $this;
  }
  

  /** @deprecated */
  public function setTitle($title) {
    $this->title = $title;
    // $this->ogImage($title);
  }

  public function suffix($callback) {
    $c_suffix = new Suffix($this);
    $callback($c_suffix);
    
    return $this;
  }

  public function create_image($callback) {
    $c_img = new Image($this);
    $callback($c_img);
    return $this;
  }

  public function update() {
    mMeta::where('id', $this->id)->update([
      'title'=>$this->title,
      'keywords'=>$this->keywords,
      'description'=>$this->description,
      'image'=>$this->og->image,
      'path'=>$this->path
    ]);
  }

  public function toArray() {
    // $obj = (array)$this;
    $meta = [];
    $og = [];
    $twitter = [];

    foreach($this as $k => $v){
      switch($k) {
        case 'id': case 'path': case 'created_at': case 'updated_at': break;
        // case 'revisitAfter': $meta['revisit-after'] = $v; break;
        case 'title':$og[$k] = $v;$twitter[$k] = $v;// $meta[$k] = $v;
          break;
        case 'description':$meta[$k] = $v;$og[$k] = $v;$twitter[$k] = $v;break;
        case 'title':break;
        case 'og': 
          foreach($v as $_k => $_v) {
            
            if($_k == 'image') {
              $_v = config('app.url').$_v;
              $og[$_k] = $_v;
              $twitter[$_k] = $_v;
            }else {
              $og[$_k] = $_v;
            }
          }
          break;
        case 'twitter': 
          foreach($v as $_k => $_v) {
            $twitter[$_k] = $_v;
          }
          break;
        default: $meta[$k] = $v; break;
      }
    }
    return [$meta, $og, $twitter];
  }

  // public function add($name, $value) {
  //   $this->{$name} .= $value;
  //   return $this;
  // }
}

