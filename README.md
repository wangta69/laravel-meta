# Meta manager for laravel

> 라라벨용 html meta tag 관리 프로그램입니다. SEO 최적화, SNS 공유 카드, 자동 사이트맵 생성 등 웹사이트의 메타 정보를 통합 관리하는 강력한 솔루션을 제공합니다.

## Installation

```bash
composer require wangta69/laravel-meta
php artisan pondol:install-meta
```

## How to Use

### 1. 메타 정보 가져오기 & 실시간 설정

가장 일반적인 사용법입니다. 컨트롤러에서 `Meta::get()`을 호출하여 현재 페이지에 해당하는 메타 정보를 가져온 후, 체이닝(Chaining) 방식으로 `title`, `description` 등을 실시간으로 설정합니다.

- **Controller Example (`TodayController.php`)**

```php
use Pondol\Meta\Facades\Meta;

class TodayController extends Controller
{
    public function index()
    {
        $profileName = "홍길동";
        $todaySummary = "새로운 기회가 찾아오는 날입니다.";

        $meta = Meta::get()
            ->title($profileName . '님의 오늘의 운세 (' . date('n월 j일') . ')')
            ->description($todaySummary . ' ' . $profileName . '님을 위한 오늘의 운세 종합 브리핑입니다.')
            ->keywords($profileName . ' 운세, 오늘의 운세, 띠별 운세, 별자리 운세');

        return view('view-blade', ['meta' => $meta]);
    }
}
```

- **Blade Example (`view-blade.blade.php`)**

```blade
<head>
    ...
    <title>@yield('title', $meta->title)</title>
    <x-pondol-meta::meta :meta="$meta"/>
    ...
</head>
```

---

### 2. 고급 SEO 기능

#### Canonical URL (대표 URL) 설정

`?page=2` 와 같이 파라미터가 붙거나 내용이 유사한 여러 페이지가 있을 때, 검색 엔진에 **"이 페이지의 진짜 원본은 이것이다"** 라고 알려주는 매우 중요한 SEO 기능입니다.

- **Controller Example (`CalendarController.php`)**

```php
$meta = Meta::get()
    ->title('2025년 11월 음력 달력')
    // '오늘'이 속한 달의 URL을 대표 URL로 지정
    ->canonical(route('calendar.lunar', ['year' => date('Y'), 'month' => date('m')]));
```

- **결과 HTML:**

```html
<link
  rel="canonical"
  href="https://yourdomain.com/calendar/lunar?year=2025&month=11"
/>
```

#### Robots 태그 설정

검색 엔진의 수집(crawling) 및 색인(indexing) 동작을 제어합니다. 기본값은 `index,follow` 입니다.

- **Controller Example (개인정보 수정 페이지 등)**

```php
$meta = Meta::get()
    ->title('개인정보 수정')
    // 이 페이지는 검색 결과에 노출시키지 않음
    ->robots('noindex, nofollow');
```

- **결과 HTML:**

```html
<meta name="robots" content="noindex, nofollow" />
```

---

### 3. SNS 공유 (OG Image)

카카오톡이나 페이스북으로 공유될 때 보이는 OG 이미지를 설정합니다.

#### 기존 이미지 사용

```php
$meta = Meta::get()->image('/storage/images/my_og_image.jpg');
```

#### 실시간 텍스트 이미지 생성

운세 결과처럼 개인화된 텍스트를 담은 이미지를 동적으로 생성하여 공유 효과를 극대화할 수 있습니다.

```php
$meta = Meta::get()
    ->title('홍길동님의 2025년 토정비결')
    ->create_image(function($image) {
        // 이미지에 삽입될 텍스트 (줄바꿈 가능)
        $image->text = "홍길동님의 2025년\n새로운 기회가 가득한 한 해!";

        // (선택사항) 기본 설정 외에 실시간으로 변경 가능
        // $image->background_image = '/path/to/custom_background.jpg';
        // $image->font = '/path/to/custom_font.ttf';
        // $image->fontSize = 48;

        $image->create();
    });
```

_(기본 이미지 설정은 `config/pondol-meta.php` 파일에서 변경할 수 있습니다.)_

---

### 4. 데이터베이스 연동 및 관리자 페이지

`Meta::get()`은 현재 라우트 정보를 기반으로 `metas` 데이터베이스 테이블에서 정보를 조회합니다. 만약 정보가 없으면 새롭게 생성하고, 있다면 기존 정보를 가져옵니다.

#### 데이터베이스에 메타 정보 저장/수정

게시물이나 상품처럼 콘텐츠가 생성/수정될 때 `Meta::set()`을 사용하여 메타 정보를 데이터베이스에 직접 저장하거나 업데이트할 수 있습니다.

- **Controller Example (`ItemController.php`)**

```php
public function store(Request $request)
{
    // ... (상품 저장 로직) ...

    Meta::set('items.show', ['item' => $item->id]) // 라우트 이름과 파라미터
        ->title($item->name)
        ->description($item->description)
        ->image($item->main_image_url)
        ->update(); // 데이터베이스에 저장/업데이트
}
```

#### 관리자 페이지

`config/pondol-meta.php`에서 접근 권한을 설정한 후, 아래 URL로 접속하여 사이트의 모든 페이지에 대한 메타 정보를 웹에서 직접 관리할 수 있습니다.

```
yourDomain/meta/admin
```

---

## SiteMap

Pondol Meta는 `metas` 데이터베이스에 저장된 모든 URL을 기반으로 검색 엔진 제출용 사이트맵을 자동으로 생성합니다.

- **제공 URL:**

```
yourDomain/meta/google.xml
yourDomain/meta/naver.xml
```

_(사이트맵 URL 경로는 `config/pondol-meta.php`의 `'route_sitemap.prefix'` 설정에서 변경할 수 있습니다.)_
