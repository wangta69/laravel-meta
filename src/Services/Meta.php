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
     * [신규/개선] 지능형 설명문 설정
     * 데이터가 있을 때만 해당 문구를 포함하여 설명을 구성합니다.
     * 예: $meta->smartDescription('기질 분석', $charms, '매력 포인트(:data)');
     */
    public function smartDescription(string $base, array $data = [], string $template = '', string $suffix = '')
    {
        $result = $base;

        if (! empty($data)) {
            $dataString = implode(', ', array_slice($data, 0, 3)); // 최대 3개까지만
            if ($template && str_contains($template, ':data')) {
                $result .= ' '.str_replace(':data', $dataString, $template);
            } else {
                $result .= ' '.$dataString;
            }
        }

        if ($suffix) {
            $result .= ' '.$suffix;
        }

        // 불필요한 연속 공백 및 쉼표 정리
        $this->description = Str::squish(trim($result));

        return $this;
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
        // 1. 현재 저장된 데이터 가져오기
        $currentSchema = (array) $this->structuredData;

        // 2. 외부 데이터($data)가 들어왔을 때 병합 로직
        if ($data) {
            // [CASE 1] 외부 데이터가 '다중 스키마(배열 리스트)'인 경우 (예: [Schema A, Schema B])
            if (isset($data[0])) {
                if (! empty($currentSchema)) {
                    // 기존 데이터가 단일 객체라면 리스트로 감쌈
                    $currentList = isset($currentSchema[0]) ? $currentSchema : [$currentSchema];
                    $currentSchema = array_merge($currentList, $data);
                } else {
                    $currentSchema = $data;
                }
            }
            // [CASE 2] 외부 데이터가 '단일 스키마'인 경우
            else {
                // 기존 데이터가 비어있으면 그대로 할당
                if (empty($currentSchema)) {
                    $currentSchema = $data;
                }
                // 기존 데이터가 '다중 스키마(리스트)'라면 배열 끝에 추가
                elseif (isset($currentSchema[0])) {
                    $currentSchema[] = $data;
                }
                // [CASE 3] 기존 데이터도 단일, 외부 데이터도 단일 -> 지능형 병합 (Smart Merge)
                else {
                    $currentType = $currentSchema['@type'] ?? $this->structuredDataType;
                    $incomingType = $data['@type'] ?? null;

                    // (3-1) 기존이 Product이고, 들어온 것이 다른 타입(Quiz 등)이라면 -> mainEntity로 감싸기
                    if ($currentType === 'Product' && $incomingType && $incomingType !== 'Product') {
                        // 이미지 필드는 안전하게 병합 (Product에도 이미지가 있으면 좋음)
                        if (isset($data['image'])) {
                            $this->mergeImage($currentSchema, $data['image']);
                            unset($data['image']);
                        }

                        // 나머지는 mainEntity로 병합
                        if (isset($currentSchema['mainEntity'])) {
                            // 이미 mainEntity가 있다면 배열로 변환해서 추가
                            $existingMain = isset($currentSchema['mainEntity'][0]) ? $currentSchema['mainEntity'] : [$currentSchema['mainEntity']];
                            $existingMain[] = $data;
                            $currentSchema['mainEntity'] = $existingMain;
                        } else {
                            $currentSchema['mainEntity'] = $data;
                        }
                    }
                    // (3-2) 서로 다른 독립적인 타입이라면 (예: CollectionPage + FAQPage) -> 리스트(@graph)로 변환
                    elseif ($currentType && $incomingType && $currentType !== $incomingType) {
                        $currentSchema = [$currentSchema, $data];
                    }
                    // (3-3) 같은 타입이거나 단순 속성 추가 -> 일반 병합
                    else {
                        if (isset($data['image'])) {
                            $this->mergeImage($currentSchema, $data['image']);
                            unset($data['image']);
                        }
                        $currentSchema = array_merge($currentSchema, $data);
                    }
                }
            }
        }

        // 3. 자동 스키마(기본값) 생성 (기존 데이터가 하나도 없을 때만 적용)
        // 기존 코드의 switch 문 로직을 그대로 유지
        if (empty($currentSchema) && $this->structuredDataType) {
            $autoSchema = [
                '@context' => 'https://schema.org',
                '@type' => $this->structuredDataType,
                'name' => $this->title,
                'description' => $this->description,
                'url' => url()->current(),
            ];

            switch ($this->structuredDataType) {
                case 'Article':
                    $autoSchema['headline'] = $this->title;
                    $autoSchema['image'] = $this->og->image ? url($this->og->image) : null;
                    $autoSchema['author'] = config('pondol-meta.structured_data.author');
                    $autoSchema['publisher'] = config('pondol-meta.structured_data.publisher');
                    $autoSchema['datePublished'] = $this->created_at ? $this->created_at->toIso8601String() : null;
                    $autoSchema['dateModified'] = $this->updated_at ? $this->updated_at->toIso8601String() : null;
                    break;
                case 'Service':
                    $autoSchema['provider'] = config('pondol-meta.structured_data.publisher');
                    $autoSchema['image'] = $this->og->image ? url($this->og->image) : null;
                    break;
                case 'WebPage':
                    $autoSchema['publisher'] = config('pondol-meta.structured_data.publisher');
                    break;
                case 'FAQPage':
                    if (! empty($this->faqItems)) {
                        $autoSchema['mainEntity'] = $this->faqItems;
                    }
                    break;
            }

            // FAQItems가 있는데 타입이 FAQPage가 아니라면 mainEntity에 추가
            if (! empty($this->faqItems) && $this->structuredDataType !== 'FAQPage') {
                $autoSchema['mainEntity'] = $this->faqItems;
            }

            $currentSchema = $autoSchema;
        }

        // 4. [이미지 자동 주입] 스키마에 이미지가 없고 OG 이미지가 있다면 주입 (단일 객체일 때만)
        // 리스트(@graph)인 경우 첫 번째 요소에 주입
        if (! empty($this->og->image)) {
            $targetImage = url($this->og->image);
            if (isset($currentSchema[0])) { // @graph 리스트인 경우 첫 번째 요소 업데이트
                $currentSchema[0]['image'] = [$targetImage];
            } else { // 단일 객체인 경우 업데이트
                $currentSchema['image'] = [$targetImage];
            }
        }

        // 5. 최종 반환 형태 결정 (@graph 자동 변환)
        // 숫자로 된 키(0, 1...)가 존재하면 다중 스키마이므로 @graph로 감싼다.
        if (isset($currentSchema[0])) {
            $this->structuredData = [
                '@context' => 'https://schema.org',
                '@graph' => $currentSchema,
            ];
        } else {
            // 단일 스키마라면 @context를 포함하여 저장
            // 이미 @context가 있다면 덮어씌우지 않음
            if (! isset($currentSchema['@context'])) {
                $currentSchema = array_merge(['@context' => 'https://schema.org'], $currentSchema);
            }
            $this->structuredData = $currentSchema;
        }

        // null 값 필터링 및 정규화
        $this->normalizeStructuredData();

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
            $fullUrl = (str_starts_with($path, 'http')) ? $path : url($path);
            $this->og->image = $fullUrl;
            $this->twitter->image = $fullUrl;

            // [추가] 이미 structuredData가 생성되어 있다면 해당 이미지도 즉시 변경
            if (! empty($this->structuredData)) {
                if (isset($this->structuredData['@graph'][0])) {
                    $this->structuredData['@graph'][0]['image'] = [$fullUrl];
                } elseif (isset($this->structuredData['image'])) {
                    $this->structuredData['image'] = [$fullUrl];
                }
            }
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
        if (empty($this->structuredData)) {
            return;
        }

        // [추가] 구글 리치 결과에서 별점(Review)이 허용되는 스키마 타입 목록
        $reviewableTypes = [
            'Product',
            'SoftwareApplication',
            'Book',
            'Course',
            'Event',
            'HowTo',
            'LocalBusiness',
            'Recipe',
            'CreativeWork',
        ];

        // 현재 설정된 메인 타입 (기본값 WebPage)
        $currentType = $this->structuredDataType ?? 'WebPage';

        // 다중 스키마(@graph)인 경우와 단일 스키마인 경우 분기 처리
        if (isset($this->structuredData[0]) && is_array($this->structuredData[0])) {
            // (@graph 형태: 배열의 배열)
            foreach ($this->structuredData as $key => $schema) {
                // [추가] 각 스키마별로 별점 허용 여부 체크 후 필터링
                $this->structuredData[$key] = $this->filterAggregateRating($schema, $reviewableTypes);

                // 기존 정규화 로직 (이미지, URL 등)
                $this->structuredData[$key] = $this->normalizeSchema($this->structuredData[$key]);
            }
        } else {
            // (단일 형태: 연관 배열)
            // [추가] 메인 타입이 허용 목록에 없고, 내부에 별점이 있다면 삭제
            // 단, structuredData 배열 내부에 명시된 @type이 있다면 그것을 우선 존중
            $schemaType = $this->structuredData['@type'] ?? $currentType;

            // 타입이 허용 목록에 없으면 aggregateRating 삭제
            if (! in_array($schemaType, $reviewableTypes)) {
                unset($this->structuredData['aggregateRating']);
            }

            // 기존 정규화 로직
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

    // 이미지 병합 헬퍼
    private function mergeImage(array &$schema, $newImage)
    {
        if (! isset($schema['image'])) {
            $schema['image'] = $newImage;

            return;
        }
        $existing = is_array($schema['image']) ? $schema['image'] : [$schema['image']];
        $new = is_array($newImage) ? $newImage : [$newImage];
        $schema['image'] = array_values(array_unique(array_merge($existing, $new)));
    }

    /**
     * [신규] 스키마 내부의 평점 필터링 헬퍼
     * 타입이 허용 목록에 없으면 aggregateRating을 제거합니다.
     */
    private function filterAggregateRating(array $schema, array $allowedTypes)
    {
        // 스키마에 평점이 있고, 타입(@type)이 존재할 때 검사
        if (isset($schema['aggregateRating']) && isset($schema['@type'])) {
            // 허용된 타입이 아니라면 과감히 삭제
            if (! in_array($schema['@type'], $allowedTypes)) {
                unset($schema['aggregateRating']);
            }
        }

        return $schema;
    }
}
