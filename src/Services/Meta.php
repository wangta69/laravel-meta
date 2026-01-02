<?php

namespace Pondol\Meta\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Pondol\Meta\Models\Meta as mMeta;

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

    private $structuredDataType = null; // 기본 @type을 Article로 설정

    private $faqItems = [];

    public function __construct()
    {
        $this->og = new \stdClass;
        $this->og->type = 'article'; // 'website'
        $this->og->locale = 'ko_KR';
        $this->og->site_name = config('app.name', 'OnStory');
        $this->twitter = new \stdClass;
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

        if (! $meta->path && $route_name) {
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
        if (! $route_name) {
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
     * @param  string  $content  (예: 'noindex, nofollow')
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
     * @param  string|null  $url  Canonical URL. null일 경우 자동 생성 시도.
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
     * OpenGraph URL을 강제로 설정하는 메서드
     * 컨트롤러에서 ->ogUrl(...) 로 호출 가능
     */
    public function ogUrl($url)
    {
        $this->og->url = $url;

        return $this;
    }

    /**
     * JSON-LD의 @type을 설정하는 헬퍼 메소드
     */
    public function type(string $type)
    {
        $this->structuredDataType = $type;
        // 체이닝을 위해 structuredData()를 호출하기 전에 사용 가능
        // if (isset($this->structuredData['@type'])) {
        //     $this->structuredData['@type'] = $type;
        // }

        return $this;
    }

    /**
     * FAQ 항목을 추가하는 빌더 메소드
     *
     * @return self
     */
    public function faq(\Closure $callback)
    {
        $callback($this);

        return $this;
    }

    /**
     * faq() 콜백 내부에서 사용될 헬퍼 메소드
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
     * @param  array|null  $data  Schema.org 규격에 맞는 PHP 배열 또는 null
     * @return self
     */
    public function structuredData(?array $data = null)
    {
        $autoSchema = [];
        // 파라미터가 없거나, 있더라도 @type이 없을 때만 자동 생성을 시도합니다.
        if (is_null($data) || ! isset($data['@type'])) {
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

                    // [신규] 누락되었던 속성들을 Article처럼 추가합니다.
                    $autoSchema['author'] = config('pondol-meta.structured_data.author');
                    $autoSchema['publisher'] = config('pondol-meta.structured_data.publisher');
                    $autoSchema['datePublished'] = $this->created_at ? $this->created_at->toIso8601String() : null;
                    $autoSchema['dateModified'] = $this->updated_at ? $this->updated_at->toIso8601String() : null;
                    break;
                case 'WebPage':
                    $autoSchema['name'] = $this->title;
                    $autoSchema['description'] = $this->description;
                    $autoSchema['publisher'] = config('pondol-meta.structured_data.publisher');
                    break;
                case 'FAQPage':
                    $autoSchema['mainEntity'] = $this->faqItems;
                    break;
                default:
                    $autoSchema['name'] = $this->title;
                    $autoSchema['description'] = $this->description;
                    break;
            }
            if (! empty($this->faqItems) && $this->structuredDataType !== 'FAQPage') {
                $autoSchema['mainEntity'] = $this->faqItems;
            }
        }

        $finalSchema = (array) $this->structuredData;
        $finalSchema = array_merge($finalSchema, $autoSchema);
        if ($data) {
            if (isset($data[0]) && is_array($data[0])) {
                $finalSchema = $data;
            } else {
                $finalSchema = array_merge($finalSchema, $data);
            }
        }

        $this->structuredData = array_filter($finalSchema, fn ($value) => ! is_null($value));
        $this->normalizeStructuredData(); // 후처리 함수 호출 추가

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
            'path' => $this->path,
        ]);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function toArray()
    {

        // og:url이 별도로 설정되지 않았다면, 현재 페이지의 Full URL(파라미터 포함)을 기본값으로 설정
        if (! isset($this->og->url)) {
            $this->og->url = request()->fullUrl();
        }

        $meta = [];
        $og = [];
        $twitter = [];

        foreach ($this as $key => $value) {
            if (is_array($value) || is_object($value)) {
                if ($key === 'og') {
                    foreach ($value as $og_key => $og_value) {
                        // 이미지 경로 처리 로직 강화
                        if ($og_key === 'image' && $og_value) {
                            // 1. 절대 경로(http...)가 아닌 경우에만 도메인 붙이기
                            if (! str_starts_with($og_value, 'http://') && ! str_starts_with($og_value, 'https://')) {

                                // 2. [핵심] 도메인 끝의 슬래시 제거 + 경로 앞의 슬래시 추가 보장
                                $baseUrl = rtrim(config('app.url'), '/'); // 도메인 뒤 '/' 제거
                                $path = Str::start($og_value, '/'); // 경로 앞 '/' 강제 추가

                                $og_value = $baseUrl.$path;
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

    /**
     * Product 스키마를 생성하고 설정합니다.
     *
     * @param  array  $productData  ['sku' => ..., 'price' => ...]
     * @return self
     */
    public function buildProductSchema(array $productData = [])
    {
        $this->type('Product'); // 타입을 'Product'로 설정 (이것은 $this->structuredDataType에 저장됨)
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product', // 여기서 Product를 명시
            'name' => $this->title,
            'description' => $this->description,
            'image' => $this->og->image ? url($this->og->image) : null,
            'sku' => $productData['sku'] ?? null,
            'brand' => ['@type' => 'Brand', 'name' => config('app.name', '길라잡이')],
            'offers' => [
                '@type' => 'Offer', 'url' => url()->current(), 'priceCurrency' => 'KRW',
                'price' => $productData['price'] ?? '0', 'availability' => 'https://schema.org/InStock',
                'seller' => ['@type' => 'Organization', 'name' => config('app.name', '길라잡이')],
            ],
        ];
        // buildProductSchema는 자체적으로 structuredData를 생성하므로,
        // 여기에 바로 병합해주는 것이 맞습니다.
        $this->structuredData = array_merge($this->structuredData, $schema);

        return $this;
    }

    /**
     * Article 스키마를 생성하고 설정합니다.
     *
     * @return self
     */
    public function buildArticleSchema()
    {
        $this->type('Article');
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $this->title,
            'description' => $this->description,
            'image' => $this->og->image ? [url($this->og->image)] : null,
            'author' => config('pondol-meta.structured_data.author'),
            'publisher' => config('pondol-meta.structured_data.publisher'),
            'datePublished' => $this->created_at ? $this->created_at->toIso8601String() : now()->subWeek()->toIso8601String(),
            'dateModified' => $this->updated_at ? $this->updated_at->toIso8601String() : now()->toIso8601String(),
        ];
        // buildArticleSchema는 자체적으로 structuredData를 생성하므로,
        // 여기에 바로 병합해주는 것이 맞습니다.
        $this->structuredData = array_merge($this->structuredData, $schema);

        return $this;
    }

    /**
     * CollectionPage 스키마를 생성하고 설정합니다.
     * 페이지에 포함된 링크들의 목록을 받아서 'hasPart' 속성을 자동으로 구성합니다.
     *
     * @param  array  $items  ['name' => '항목 이름', 'url' => '항목 URL'] 형태의 배열
     * @return self
     */
    public function buildCollectionPageSchema(array $items = [])
    {
        $this->type('CollectionPage');
        $hasPart = [];
        foreach ($items as $item) {
            if (isset($item['name']) && isset($item['url'])) {
                $hasPart[] = ['@type' => 'WebPage', 'name' => $item['name'], 'url' => $item['url']];
            }
        }
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $this->title,
            'description' => $this->description,
            'hasPart' => $hasPart,
        ];
        // buildCollectionPageSchema는 자체적으로 structuredData를 생성하므로,
        // 여기에 바로 병합해주는 것이 맞습니다.
        $this->structuredData = array_merge($this->structuredData, $schema);

        return $this;
    }

    /**
     * FAQPage 스키마를 생성하고 설정합니다.
     * 이 메소드는 이전에 만들었던 faq() 빌더와 함께 작동합니다.
     *
     * @return self
     */
    public function buildFaqPageSchema()
    {
        $this->type('FAQPage');
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $this->faqItems,
        ];
        // buildFaqPageSchema는 자체적으로 structuredData를 생성하므로,
        // 여기에 바로 병합해주는 것이 맞습니다.
        $this->structuredData = array_merge($this->structuredData, $schema);

        return $this;
    }

    public function applySchema(?array $data = null)
    {
        // 파라미터로 받은 데이터($data)가 있으면, 기존 데이터와 병합합니다.
        if ($data) {
            if (isset($data[0])) {
                $this->structuredData = $data;
            } else {
                $this->structuredData = array_merge($this->structuredData, $data);
            }
        }

        // 만약 아무런 structuredData가 설정되지 않았다면, 기본 스키마를 생성합니다.
        if (empty($this->structuredData)) {
            $this->buildDefaultSchema();
        }

        return $this;
    }

    /**
     * structuredData가 비어있을 때 호출될 기본 스키마 생성 헬퍼
     */
    private function buildDefaultSchema()
    {
        $this->type($this->structuredDataType ?? 'WebPage'); // 기본값이 없으면 WebPage

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $this->structuredDataType,
            'name' => $this->title,
            'description' => $this->description,
            'publisher' => config('pondol-meta.structured_data.publisher'),
        ];
        $this->structuredData = $schema;
    }

    /**
     * [신규] structuredData 내부의 값들을 최종적으로 정규화하는 헬퍼
     */
    private function normalizeStructuredData()
    {
        // structuredData가 비어있으면 아무것도 하지 않음
        if (empty($this->structuredData)) {
            return;
        }

        // 여러 스키마가 배열로 들어있는 경우, 각 스키마에 대해 재귀적으로 처리
        if (isset($this->structuredData[0]) && is_array($this->structuredData[0])) {
            foreach ($this->structuredData as $key => $schema) {
                $this->structuredData[$key] = $this->normalizeSchema($schema);
            }
        } else { // 단일 스키마 처리
            $this->structuredData = $this->normalizeSchema($this->structuredData);
        }
    }

    /**
     * 단일 스키마 객체를 정규화하는 헬퍼
     */
    private function normalizeSchema(array $schema): array
    {
        if (isset($schema['publisher']['logo']['url']) && ! Str::startsWith($schema['publisher']['logo']['url'], 'http')) {
            $schema['publisher']['logo']['url'] = url($schema['publisher']['logo']['url']);
        }
        if (isset($schema['provider']['logo']['url']) && ! Str::startsWith($schema['provider']['logo']['url'], 'http')) {
            $schema['provider']['logo']['url'] = url($schema['provider']['logo']['url']);
        }
        if (isset($schema['image'])) {
            if (is_array($schema['image'])) {
                foreach ($schema['image'] as $key => $url) {
                    if (! Str::startsWith($url, 'http')) {
                        $schema['image'][$key] = url($url);
                    }
                }
            } elseif (is_string($schema['image']) && ! Str::startsWith($schema['image'], 'http')) {
                $schema['image'] = url($schema['image']);
            }
        }

        // null 값을 가진 키를 제거
        return array_filter($schema, fn ($value) => ! is_null($value));
    }
}
