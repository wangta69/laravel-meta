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
        // 1. 자동 생성 로직을 '항상' 먼저 실행하여 기본 스키마($autoSchema)를 생성합니다.
        $autoSchema = [];
        $autoSchema['@context'] = 'https://schema.org';
        $autoSchema['@type'] = $this->structuredDataType;

        switch ($this->structuredDataType) {
            case 'Article':
                $autoSchema['headline'] = $this->title;
                $autoSchema['description'] = $this->description;
                $autoSchema['image'] = $this->og->image ? url($this->og->image) : null;
                $autoSchema['author'] = config('pondol-meta.structured_data.author');
                $autoSchema['publisher'] = config('pondol-meta.structured_data.publisher');
                $autoSchema['datePublished'] = $this->created_at ? $this->created_at->toIso8601String() : null;
                $autoSchema['dateModified'] = $this->updated_at ? $this->updated_at->toIso8601String() : null;
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

                $autoSchema['name'] = $this->title;
                $autoSchema['serviceType'] = trim($serviceType);
                $autoSchema['provider'] = config('pondol-meta.structured_data.publisher');
                $autoSchema['description'] = $this->description;
                $autoSchema['image'] = $this->og->image ? url($this->og->image) : null;
                break;
            case 'WebPage':
                $autoSchema['name'] = $this->title;
                $autoSchema['description'] = $this->description;
                $autoSchema['publisher'] = config('pondol-meta.structured_data.publisher');
                break;
            case 'FAQPage':
                // 타입이 FAQPage'만'으로 설정된 경우, mainEntity를 여기에 직접 할당합니다.
                $autoSchema['mainEntity'] = $this->faqItems;
                break;
            default:
                // Article, Service, WebPage가 아닌 다른 모든 타입의 기본값
                $autoSchema['name'] = $this->title;
                $autoSchema['description'] = $this->description;
                break;
        }

        // FAQ 데이터가 있고, 현재 타입이 FAQPage가 아닐 경우, mainEntity를 추가합니다.
        if (!empty($this->faqItems) && $this->structuredDataType !== 'FAQPage') {
            $autoSchema['mainEntity'] = $this->faqItems;
        }

        // 2. 파라미터로 받은 수동 데이터($data)를 자동 생성된 스키마($autoSchema)에 '병합'합니다.
        //    $data가 null이면 $autoSchema를 그대로 사용하고, $data가 있으면 두 배열을 합칩니다.
        //    array_merge는 뒤에 오는 배열의 값으로 키를 덮어씁니다.
        $schema = is_null($data) ? $autoSchema : array_merge($autoSchema, $data);

        // 3. 공통 후처리 로직 (URL 변환 등 - 기존과 동일)
        if (isset($schema['publisher']['logo']['url']) && !str_starts_with($schema['publisher']['logo']['url'], 'http')) {
            $schema['publisher']['logo']['url'] = url($schema['publisher']['logo']['url']);
        }
        if (isset($schema['provider']['logo']['url']) && !str_starts_with($schema['provider']['logo']['url'], 'http')) {
            $schema['provider']['logo']['url'] = url($schema['provider']['logo']['url']);
        }

        // 4. 최종적으로 클래스 속성에 데이터를 병합합니다. (null 값 제거 포함)
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
            if (is_array($value) || is_object($value)) {
                if ($key === 'og') {
                    foreach ($value as $og_key => $og_value) {
                        // [수정] 이미지 경로 처리 로직 강화
                        if ($og_key === 'image' && $og_value) {
                            // 1. 절대 경로(http...)가 아닌 경우에만 도메인 붙이기
                            if (!str_starts_with($og_value, 'http://') && !str_starts_with($og_value, 'https://')) {

                                // 2. [핵심] 도메인 끝의 슬래시 제거 + 경로 앞의 슬래시 추가 보장
                                $baseUrl = rtrim(config('app.url'), '/'); // 도메인 뒤 '/' 제거
                                $path = \Illuminate\Support\Str::start($og_value, '/'); // 경로 앞 '/' 강제 추가

                                $og_value = $baseUrl . $path;
                            }

                            $og[$og_key] = $og_value;

                            // 트위터 이미지도 동기화
                            $twitter['image'] = $og_value;
                        } else {
                            $og[$og_key] = $og_value;
                        }
                    }
                } elseif ($key === 'twitter') {
                    foreach ($value as $tw_key => $tw_value) {
                        $twitter[$tw_key] = $tw_value;
                    }
                }
                continue;
            }

            // ... (아래 스위치문은 기존과 동일) ...
            switch ($key) {
                case 'id': case 'path': case 'created_at': case 'updated_at':
                    break;
                case 'description':
                    $meta[$key] = $value;
                    $og[$key] = $value;
                    $twitter[$key] = $value;
                    break;
                case 'title':
                    $og[$key] = $value;
                    $twitter[$key] = $value;
                    break;
                default:
                    $meta[$key] = $value;
                    break;
            }
        }
        return [$meta, $og, $twitter];
    }

}
