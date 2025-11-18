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
    public $structuredData = [];
    private $structuredDataType = 'Article'; // 기본 @type을 Article로 설정
    private $faqItems = [];

    public function __construct()
    {
        $this->og = new \stdClass();
        $this->og->type = 'article'; //'website'
        $this->og->locale = 'ko_KR';
        $this->og->site_name = config('app.name', 'OnStory');

        $this->twitter = new \stdClass();
        $this->twitter->card = 'summary_large_image'; // summary

        $this->robots = config('pondol-meta.robots');
        $this->structuredData = [];

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
        $this->og->image = $meta->image ?: config('pondol-meta.defaults.image');

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

    /**
     * JSON-LD의 @type을 설정하는 헬퍼 메소드
     */
    public function type(string $type)
    {
        $this->structuredDataType = $type;
        // 체이닝을 위해 structuredData()를 호출하기 전에 사용 가능
        if (isset($this->structuredData['@type'])) {
            $this->structuredData['@type'] = $type;
        }
        return $this;
    }


    /**
     * FAQ 항목을 추가하는 빌더 메소드
     *
     * @param \Closure $callback
     * @return self
     */
    public function faq(\Closure $callback)
    {
        // Meta 객체 자신을 콜백 함수에 전달하여, 내부에서 addFaq를 호출할 수 있게 함
        $callback($this);
        return $this;
    }

    /**
     * faq() 콜백 내부에서 사용될 헬퍼 메소드
     *
     * @param string $question
     * @param string $answer
     */
    public function addFaq(string $question, string $answer)
    {
        $this->faqItems[] = [
            '@type' => 'Question',
            'name' => $question,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $answer,
            ],
        ];
    }

    /**
     * JSON-LD 구조화된 데이터를 설정합니다.
     * 파라미터 없이 호출하면, 기존 meta 정보와 type()으로 설정된 타입을 기반으로 JSON-LD를 자동 생성합니다.
     * 파라미터로 배열을 전달하면, 해당 데이터를 기존 structuredData에 병합합니다.
     *
     * @param array|null $data Schema.org 규격에 맞는 PHP 배열 또는 null
     * @return self
     */
    public function structuredData(array $data = null)
    {
        $schema = [];

        // 1. 파라미터 유무에 따라 $schema 변수 준비
        if (is_null($data)) {
            // 자동 생성 로직: 파라미터가 없으면 내부 정보를 바탕으로 $schema 배열 생성
            $schema['@context'] = 'https://schema.org';
            $schema['@type'] = $this->structuredDataType;

            switch ($this->structuredDataType) {
                case 'Article':
                    $schema['headline'] = $this->title;
                    $schema['description'] = $this->description;
                    $schema['image'] = $this->og->image ? url($this->og->image) : null;
                    $schema['author'] = config('pondol-meta.structured_data.author');
                    $schema['publisher'] = config('pondol-meta.structured_data.publisher');
                    $schema['datePublished'] = $this->created_at ? $this->created_at->toIso8601String() : null;
                    $schema['dateModified'] = $this->updated_at ? $this->updated_at->toIso8601String() : null;
                    break;
                case 'Service':
                    $serviceType = $this->title;
                    if (str_contains($serviceType, ' - ')) {
                        $serviceType = explode(' - ', $serviceType)[0];
                    }
                    if (str_contains($serviceType, '(')) {
                        $serviceType = explode(' (', $serviceType)[0];
                    }
                    if (str_contains($serviceType, '님의 ')) {
                        $serviceType = explode('님의 ', $serviceType)[1];
                    }

                    $schema['name'] = $this->title;
                    $schema['serviceType'] = trim($serviceType);
                    $schema['provider'] = config('pondol-meta.structured_data.publisher');
                    $schema['description'] = $this->description;
                    $schema['image'] = $this->og->image ? url($this->og->image) : null;
                    break;
                case 'WebPage':
                    $schema['name'] = $this->title;
                    $schema['description'] = $this->description;
                    $schema['publisher'] = config('pondol-meta.structured_data.publisher');
                    break;
                case 'FAQPage':
                    $schema['mainEntity'] = $this->faqItems;
                    break;
                default:
                    $schema['name'] = $this->title;
                    $schema['description'] = $this->description;
                    break;
            }
        } else {
            // 수동 설정: 파라미터가 있으면 그 데이터를 그대로 $schema 변수에 할당
            $schema = $data;
        }

        // 2. $schema가 어떤 방식으로 생성되었든 상관없이, 공통 후처리 로직을 항상 실행합니다.

        // 'publisher' 키 확인 및 URL 변환
        if (isset($schema['publisher']['logo']['url']) && !str_starts_with($schema['publisher']['logo']['url'], 'http')) {
            $schema['publisher']['logo']['url'] = url($schema['publisher']['logo']['url']);
        }
        // 'provider' 키 확인 및 URL 변환 (Service 타입용)
        if (isset($schema['provider']['logo']['url']) && !str_starts_with($schema['provider']['logo']['url'], 'http')) {
            $schema['provider']['logo']['url'] = url($schema['provider']['logo']['url']);
        }

        // 3. 최종적으로 클래스 속성에 데이터를 할당합니다.
        // 값이 null인 키는 제거하고, 기존 데이터와 병합합니다.
        $this->structuredData = array_merge((array) $this->structuredData, array_filter($schema, fn ($value) => !is_null($value)));

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
        $meta = [];
        $og = [];
        $twitter = [];

        foreach ($this as $key => $value) {
            // 배열이나 객체인 속성은 $meta 배열에 포함시키지 않도록 조건을 추가합니다. ▼▼▼
            if (is_array($value) || is_object($value)) {
                // og, twitter 객체는 별도로 처리
                if ($key === 'og') {
                    foreach ($value as $og_key => $og_value) {
                        if ($og_key === 'image' && $og_value) {
                            $og_value = config('app.url') . $og_value;
                            $og[$og_key] = $og_value;
                            $twitter[$og_key] = $og_value;
                        } else {
                            $og[$og_key] = $og_value;
                        }
                    }
                } elseif ($key === 'twitter') {
                    foreach ($value as $tw_key => $tw_value) {
                        $twitter[$tw_key] = $tw_value;
                    }
                }
                // structuredData (배열) 등 다른 객체/배열 속성은 무시하고 넘어갑니다.
                continue;
            }

            // 문자열 또는 숫자 등 스칼라 타입의 값만 $meta 배열에 할당됩니다.
            switch ($key) {
                case 'id': case 'path': case 'created_at': case 'updated_at':
                    break; // $meta 배열에 포함시키지 않을 속성들

                case 'description':
                    $meta[$key] = $value;
                    $og[$key] = $value;
                    $twitter[$key] = $value;
                    break;

                case 'title':
                    $og[$key] = $value;
                    $twitter[$key] = $value;
                    // $meta 배열에는 title을 넣지 않습니다. <title> 태그에서 별도로 사용하기 때문입니다.
                    break;

                default:
                    $meta[$key] = $value;
                    break;
            }
        }
        return [$meta, $og, $twitter];
    }

}
