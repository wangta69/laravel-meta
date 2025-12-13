This library is used in the production of [gilra.kr](https://www.gilra.kr) (Online Fortune Service).

# Meta manager for laravel

> 라라벨용 HTML 메타 태그 관리 프로그램입니다. SEO 최적화, SNS 공유 카드, **JSON-LD 구조화된 데이터**, 자동 사이트맵 생성 등 웹사이트의 메타 정보를 통합 관리하는 강력한 솔루션을 제공합니다.

## Installation

```bash
composer require wangta69/laravel-meta
php artisan pondol:install-meta
```

_설치 후 `config/pondol-meta.php` 파일에서 사이트의 기본 설정을 확인하세요._

## How to Use

`Pondol\Meta` 패키지는 컨트롤러에서 `Meta` Facade를 사용하여 매우 쉽고 직관적으로 사용할 수 있습니다.

### 1. 실시간 메타 정보 설정

#### 1.1 기본 설정

컨트롤러에서 `Meta::get()`을 호출하여 현재 페이지의 메타 객체를 가져온 후, 체이닝(Chaining) 방식으로 `title`, `description` 등을 설정합니다.

- **Controller Example (블로그 게시물 페이지)**

```php
use Pondol\Meta\Facades\Meta;

class PostController extends Controller
{
    public function show($id)
    {
        $post = Post::findOrFail($id);

        $meta = Meta::get()
            ->title($post->title . ' - ' . config('app.name'))
            ->description(Str::limit($post->content, 155))
            ->keywords($post->tags->pluck('name')->implode(', '));

        return view('posts.show', ['meta' => $meta, 'post' => $post]);
    }
}
```

- **Blade Example (`posts/show.blade.php`)**

```blade
<head>
    ...
    <title>@yield('title', $meta->title)</title>
    <x-pondol-meta::meta :meta="$meta"/>
    ...
</head>
```

---

#### 1.2. 고급 SEO 기능

#### Canonical URL (대표 URL) 설정

유사한 콘텐츠를 가진 여러 URL 중, 검색 엔진에 "이 페이지가 원본이다"라고 알려주는 중요한 SEO 태그입니다.

```php
$meta = Meta::get()
    ->title('자유 게시판 - 2페이지')
    // 파라미터가 없는 기본 URL을 대표 URL로 지정
    ->canonical(route('posts.index'));
```

- **결과 HTML (`/posts?page=2` 에서):**

```html
<link rel="canonical" href="https://yourdomain.com/posts" />
```

#### 1.3. Robots 태그 설정

검색 엔진의 수집 및 색인 동작을 제어합니다. (기본값: `index,follow`)

```php
$meta = Meta::get()
    ->title('이벤트 준비중')
    // 이 페이지는 검색 결과에 노출시키지 않음
    ->robots('noindex, nofollow');
```

- **결과 HTML:**

```html
<meta name="robots" content="noindex, nofollow" />
```

---

#### 1.4. JSON-LD 구조화된 데이터 (강력 추천)

검색 결과에서 별점, FAQ 등 '리치 스니펫'을 표시하여 클릭률을 높이는 가장 강력한 SEO 기능입니다.

#### 방법 1: 자동 생성 (가장 쉬운 방법)

`->title()`, `->description()` 등을 설정한 후, `->type()`으로 타입을 지정하고 마지막에 **`->structuredData()`**를 파라미터 없이 호출하면 끝입니다.

- **Controller Example (`Service` 타입)**

```php
$meta = Meta::get()
    ->title('프리미엄 세차 서비스 - 카 워시') // '프리미엄 세차 서비스'를 serviceType으로 자동 추출
    ->description('최고급 왁스를 사용한 디테일링 세차 서비스입니다.')
    ->type('Service')      // 1. @type을 'Service'로 지정
    ->structuredData();   // 2. 자동 생성 실행!
```

#### 1.5. FAQ 빌더 (Q&A 페이지에 최적)

`->type('FAQPage')`와 함께 `->faq()` 빌더를 사용하면, 복잡한 배열 없이도 직관적으로 FAQ 스키마를 만들 수 있습니다.

- **Controller Example (`FaqController.php`)**

```php
$meta = Meta::get()
    ->title('자주 묻는 질문 - 고객센터')
    ->description('서비스 이용에 대한 모든 궁금증을 해결해 드립니다.')
    ->type('FAQPage')
    ->faq(function($meta) {
        $meta->addFaq('환불 규정은 어떻게 되나요?', '서비스 이용 전에는 100% 환불이 가능합니다.');
        $meta->addFaq('해외에서도 사용 가능한가요?', '네, 전 세계 어디서든 이용 가능합니다.');
    })
    ->structuredData();
```

#### 1.6. 커스텀 스키마

`Article`, `Service`, `FAQPage` 외의 다른 타입을 사용하고 싶을 경우, 직접 `Schema.org` 규격에 맞는 배열을 만들어 전달할 수 있습니다.

```php
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => 'Awesome T-Shirt',
    // ... (상품 관련 다른 스키마 정보)
];

$meta = Meta::get()->structuredData($schema);
```

---

#### 1.7. SNS 공유 (OG Image)

##### 기존 이미지 사용

```php
$meta = Meta::get()->image('/storage/images/default-og-image.jpg');
```

##### 실시간 텍스트 이미지 생성

개인화된 텍스트를 담은 이미지를 동적으로 생성하여 SNS 공유 효과를 극대화합니다.

```php
$meta = Meta::get()
    ->title('새로운 이벤트에 당신을 초대합니다!')
    ->create_image(function($image) {
        $image->text = "Awesome Shop\n신규 회원 20% 할인 이벤트";
        $image->create();
    });
```

_(기본 배경, 폰트 등은 `config/pondol-meta.php`에서 설정할 수 있습니다.)_

---

### 2. 관리자 페이지에서 관리하기

#### 관리자 페이지

`config/pondol-meta.php`에서 접근 권한을 설정한 후, 아래 URL로 접속하여 사이트의 모든 페이지에 대한 메타 정보를 웹에서 직접 관리할 수 있습니다.

```
yourDomain/meta/admin
```

#### 2.1 데이터베이스에 메타 정보 저장/수정 (`Meta::set()`)

콘텐츠가 생성/수정될 때 `Meta::set()`을 사용하여 메타 정보를 데이터베이스에 영구적으로 저장하거나 업데이트할 수 있습니다.

- **Controller Example**

```php
public function store(Request $request)
{
    // ... (게시물 저장 로직) ...
    $post = Post::create(...);

    Meta::set('posts.show', ['post' => $post->id])
        ->title($post->title)
        ->description(Str::limit($post->content, 155))
        ->update(); // DB에 저장
}
```

### 3. 프론트 페이지에서 관리하기

프론트 페이지에서 현재 페이지를 보면서 관리 하는 방식입니다.

#### 3.1 Component 세팅

원하는 페이지에서 아래 component를 blade에 넣어두면 관리자 권하시 입력폼이 디스플레이 됩니다.

```
<x-pondol-meta::edit-auth :meta="$meta ?? null" />

```

#### 3.2 Indexnow 로 보내기

`<`x-pondol-meta::edit-auth `/>` 를 사용할때는 자동으로 설정되나 indexnow로 전송을 별도로 하고 싶을 경우 아래 component를 호출하면 됩니다.

```
<x-pondol-meta::indexnow-auth :meta="$meta ?? null" />
```

---

## SiteMap

`metas` 데이터베이스에 저장된 모든 URL을 기반으로 검색 엔진 제출용 사이트맵을 자동으로 생성합니다.

- **제공 URL:**

```
yourDomain/meta/google.xml
yourDomain/meta/naver.xml
```

_(사이트맵 URL 경로는 `config/pondol-meta.php`의 `'route_sitemap.prefix'` 설정에서 변경할 수 있습니다.)_
