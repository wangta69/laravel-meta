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
    public $og;
    public $twitter;
    public $robots = 'index,follow';
    public $canonical = null;

    public function __construct()
    {
        $this->og = new \stdClass();
        $this->og->type = 'article'; //'website'
        $this->og->locale = 'ko_KR';
        $this->og->site_name = config('app.name', 'OnStory');

        $this->twitter = new \stdClass();
        $this->twitter->card = 'summary_large_image'; // summary

        $this->robots = config('pondol-meta.robots');
    }

    /**
     * 게시물 등록이나 수정시 사용
     */
    public function set($route_name, $route_params)
    {

        $meta = mMeta::firstOrCreate(['name' => $route_name, 'params' => json_encode($route_params)]);
        $this->id = $meta->id;
        $this->title = $meta->title;
        $this->keywords = $meta->keywords;
        $this->description = $meta->description;

        $this->created_at = $meta->created_at;
        $this->updated_at = $meta->updated_at;
        $this->og->image = $meta->image;

        if (!$meta->path && $route_name) {
            try {
                $meta->path = str_replace(config('app.url'), '', route($route_name, $route_params));

                $meta->save();
            } catch (\Exception $e) {
                \Log::debug($e->getMessage());
            }
        }

        $this->path = $meta->path;
        return $this;
    }

    public function get()
    {

        $route_name = Route::currentRouteName();
        // $type='route';
        if (!$route_name) {
            $route_name = request()->path();
            // $type='path';
        }

        $route_params = [];
        foreach (Route::getCurrentRoute()->parameterNames as $p) {
            $route_params[$p] = Route::getCurrentRoute()->originalParameter($p);
        }
        return $this->set($route_name, $route_params);
    }

    public function title($title)
    {
        if ($title) {
            $this->title = $title;
        }
        return $this;
    }


    public function keywords($keywords)
    {
        if ($keywords) {
            $this->keywords = $keywords;
        }
        return $this;
    }

    /**
     * robots 메타 태그의 content 값을 설정하는 메소드
     *
     * @param string $content (예: 'noindex, nofollow')
     * @return self
     */
    public function robots(string $content)
    {
        $this->robots = $content;
        return $this;
    }


    /**
     * Canonical URL을 설정하는 메소드
     *
     * @param string|null $url Canonical URL. null일 경우 자동 생성 시도.
     * @return self
     */
    public function canonical($url = null)
    {
        if ($url) {
            // URL이 직접 제공된 경우, 해당 URL을 사용
            $this->canonical = $url;
        } else {
            // URL이 제공되지 않은 경우, 현재 라우트의 파라미터 없이 URL을 생성 (기본값)
            try {
                $this->canonical = route(Route::currentRouteName());
            } catch (\Exception $e) {
                // 라우트 파라미터가 필수인 경우 오류가 발생할 수 있으므로 예외 처리
                $this->canonical = request()->url();
            }
        }

        return $this;
    }

    // 2차 배열용
    public function extractKeywordsFromArray($arr, $key)
    {
        $keywords = [];
        foreach ($arr as $val) {
            foreach ($val as $k => $v) {
                if ($k == $key) {
                    array_push($keywords, $v);
                }
            }
        }
        $this->keywords = implode(',', $keywords);
        return $this;
    }
    public function description($description)
    {
        if ($description) {
            $this->description = $description;
        }
        return $this;
    }
    public function image($path)
    {
        if ($path) {
            $this->og->image = $path;
        }
        return $this;
    }
    /** @deprecated */
    public function path($path)
    {
        $this->path = $path ?? $this->path;
        return $this;
    }


    /** @deprecated */
    public function setTitle($title)
    {
        $this->title = $title;
        // $this->ogImage($title);
    }

    public function suffix($callback)
    {
        $c_suffix = new Suffix($this);
        $callback($c_suffix);

        return $this;
    }

    public function create_image($callback)
    {
        $c_img = new Image($this);
        $callback($c_img);
        return $this;
    }

    public function update()
    {
        mMeta::where('id', $this->id)->update([
          'title' => $this->title,
          'keywords' => $this->keywords,
          'description' => $this->description,
          'image' => $this->og->image,
          'path' => $this->path
        ]);
    }

    public function toArray()
    {
        // $obj = (array)$this;
        $meta = [];
        $og = [];
        $twitter = [];

        foreach ($this as $k => $v) {
            switch ($k) {
                case 'id': case 'path': case 'created_at': case 'updated_at': break;
                    // case 'revisitAfter': $meta['revisit-after'] = $v; break;
                case 'title':$og[$k] = $v;
                    $twitter[$k] = $v;// $meta[$k] = $v;
                    break;
                case 'description':$meta[$k] = $v;
                    $og[$k] = $v;
                    $twitter[$k] = $v;
                    break;
                case 'title':break;
                case 'og':
                    foreach ($v as $_k => $_v) {

                        if ($_k == 'image') {
                            $_v = config('app.url').$_v;
                            $og[$_k] = $_v;
                            $twitter[$_k] = $_v;
                        } else {
                            $og[$_k] = $_v;
                        }
                    }
                    break;
                case 'twitter':
                    foreach ($v as $_k => $_v) {
                        $twitter[$_k] = $_v;
                    }
                    break;
                default: $meta[$k] = $v;
                    break;
            }
        }
        return [$meta, $og, $twitter];
    }

}
