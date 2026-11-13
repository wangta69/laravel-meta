# Meta Manager for Laravel

> **Laravel Meta**는 라라벨 애플리케이션을 위한 종합 SEO 솔루션입니다. 단순한 태그 삽입을 넘어, DB 기반 관리 시스템, 실시간 이미지 생성, 지능형 JSON-LD 엔진, 그리고 **IndexNow**를 통한 즉각적인 검색 엔진 색인 기능을 제공합니다.

## Installation

```bash
composer require wangta69/laravel-meta
php artisan pondol:install-meta
```

---

## 1. 기본 적인 사용법

- 컨트롤러

```php
use Pondol\Meta\Facades\Meta;

---
$meta = Meta::get()
            ->title('타이틀')
            ->description('요약 설명글')
            ->keywords('키워드1, 키워드2,... 컴마로 구분')
            ->image('이미지 경로')
            ->canonical('캐노니컬')
            ->type('page 속성')
            ->structuredData();

---
return view('home', compact('meta'));
```

- blade(viewer)

```blade
@extends('layouts/app')

@section('title', $meta->title ?? '')
@section('meta_tags')
    <x-pondol-meta::meta :meta="$meta" />
@endsection
....
```

- blade(app)

```blade
@yield('meta_tags')

<title>@yield('title', config('app.name', '온스토리'))</title>
```

## 2. 메타 데이터 관리 (Database & Real-time)

### A. 데이터베이스 기반 관리

라우트 정보를 기준으로 메타 데이터를 저장하여 관리자 페이지에서 수정 가능하게 합니다.

```php
Meta::set('market.item', ['item' => (string)$item->id])
    ->title($item->name)
    ->description($item->short_desc)
    // 2차원 배열에서 특정 키값을 추출하여 키워드로 자동 변환
    ->extractKeywordsFromArray($item->tags, 'tag')
    ->image(\Storage::url($item->image))
    ->update();
```

### B. 실시간 메타 수정 및 로드

현재 페이지의 라우트를 분석해 자동으로 데이터를 가져오며, 즉석에서 내용을 변경할 수 있습니다.

```php
// Controller
$meta = Meta::get()->title($request->q . ' 검색결과');

return view('search', compact('meta'));
```

---

## 3. 블레이드 컴포넌트 (Blade Components)

헤더 태그 출력뿐만 아니라, 검색 엔진에 콘텐츠 변경을 알리고 관리자 권한으로 페이지 내에서 바로 메타 정보를 수정할 수 있는 전용 컴포넌트를 제공합니다.

```html
<!-- 헤더 영역: 메타 태그, OG, Twitter, JSON-LD 자동 출력 -->
<x-pondol-meta::meta :meta="$meta" />

<!-- 본문 영역: 네이버/빙 등 검색엔진 자동 색인(IndexNow) 및 인증 처리 -->
<x-pondol-meta::indexnow-auth :meta="$meta ?? null" />

<!-- 본문 영역: 관리자 권한 확인 후 실시간 메타 정보 수정 폼 출력 -->
<x-pondol-meta::edit-auth :meta="$meta" />
```

### 🎨 스킨 설정 및 커스터마이징 (Skins)

`edit-auth` 및 `edit` 컴포넌트는 **스킨(Skin)** 시스템을 지원하며, 패키지에 기본적으로 두 가지 스킨이 포함되어 있습니다.

- **기본 제공 스킨**
  - `default`: **Bootstrap 5** 기반 스킨
  - `tailwind`: **Tailwind CSS (v3/v4 호환)** 기반 스킨

#### A. 전역 기본 스킨 설정 (`config/pondol-meta.php`)

애플리케이션 전체에서 사용할 기본 스킨을 설정 파일에서 지정할 수 있습니다.

```php
// config/pondol-meta.php
'skin' => 'default', // 'default', 'tailwind' 또는 커스텀 스킨명
```

#### B. 컴포넌트 호출 시 특정 스킨 지정

페이지나 레이아웃에 따라 호출 시점에 `skin` 속성을 직접 지정할 수 있습니다. (전달된 `skin`이 설정 파일보다 우선 적용됩니다.)

```html
<!-- Tailwind CSS 스킨 사용 -->
<x-pondol-meta::edit-auth :meta="$meta" skin="tailwind" />

<!-- 추가 HTML 속성(class 등) 전달 가능 -->
<x-pondol-meta::edit-auth :meta="$meta" skin="tailwind" class="mt-10" />

<!-- 권한 체크 없이 수정 폼 컴포넌트만 단독 사용할 경우 -->
<x-pondol-meta::edit :meta="$meta" skin="default" />

<!-- 프로젝트 내 일반 뷰 경로 직접 지정도 가능 -->
<x-pondol-meta::edit-auth :meta="$meta" skin="partials.custom-meta-edit" />
```

#### C. 스킨 수정 및 새 스킨 추가

`php artisan pondol:install-meta` 실행 시 스킨 파일이 프로젝트의 `resources/views/vendor/pondol-meta/skins/` 경로로 자동 복사됩니다. (스킨만 별도로 다시 복사하려면 아래 명령어를 실행하세요.)

```bash
php artisan vendor:publish --tag=pondol-meta-skins
```

1. **기존 스킨 수정**: `resources/views/vendor/pondol-meta/skins/default/edit.blade.php` (또는 `tailwind/edit.blade.php`) 파일을 수정합니다.
2. **새 스킨 추가**: `resources/views/vendor/pondol-meta/skins/{원하는스킨명}/edit.blade.php` 파일을 생성한 뒤, 설정 파일이나 `skin="{원하는스킨명}"` 속성으로 불러와 사용할 수 있습니다.

---

## 4. 강력한 SEO 도구 (Advanced SEO)

### ✨ Smart Description (지능형 문구 생성)

데이터 유무에 따라 문장을 자연스럽게 조합하여 `매력포인트()`와 같은 빈 괄호 노출을 방지합니다.

```php
$meta->smartDescription(
    "사주 분석 결과.",
    $charms, // 배열 데이터
    "숨겨진 매력 포인트(:data) 확인!", // 데이터 존재 시에만 문구 삽입
    "지금 바로 확인해보세요."
);
```

### 🏷️ 제목 접미사 (Suffix)

페이지 번호 등 기존 제목 뒤에 공통적으로 붙는 텍스트를 처리합니다.

```php
Meta::get()->suffix(function($suffix) use($page) {
    $suffix->title = ' - ' . $page . '페이지';
});
```

---

## 5. 지능형 JSON-LD (Structured Data)

Schema.org 규격에 맞는 구조화 데이터를 지원합니다. 여러 타입이 섞여도 지능적으로 병합(Smart Merge)하여 구글 리치 결과를 생성합니다.

- **지원 타입**: Article, Person, Product, Service, FAQPage 등

```php
Meta::get()
    ->type('Person')
    ->structuredData([
        'name' => '스타 이름',
        'jobTitle' => '가수',
        'memberOf' => ['@type' => 'Organization', 'name' => '그룹명']
    ])
    ->faq(function($meta) {
        $meta->addFaq("질문 내용", "답변 내용");
    });
```

---

## 6. 이미지 관리 (Static & Dynamic)

### A. 기존 이미지 경로 설정

```php
Meta::get()->image('/path/to/image.jpg');
```

### B. 실시간 텍스트 이미지 생성 (Dynamic OG Image)

설정된 배경 위에 텍스트가 올라간 이미지를 즉석에서 생성합니다.

```php
Meta::get()->create_image(function($image) {
    $image->save_path = 'public/meta/banner.png'; // 저장 경로
    $image->background_image = public_path('images/bg.jpg'); // 배경
    $image->font = public_path('fonts/Nanum.ttf'); // 폰트
    $image->fontSize = 25; // 크기
    $image->create();
});
```

---

## 7. 관리소 및 사이트맵 (Admin & Sitemap)

### 관리자 페이지

웹에서 라우트별 메타 정보를 직접 수정할 수 있습니다. `config/pondol-meta.php`에서 접근 권한 설정 후 접속하세요.

- **URL**: `yourDomain/meta/admin`

### XML 사이트맵

검색 엔진 최적화의 필수인 사이트맵을 자동 생성합니다.

- **Google**: `yourDomain/meta/google.xml`
- **Naver**: `yourDomain/meta/naver.xml`

---

## Demo (데모 사이트)

본 패키지의 모든 기능(Meta, JSON-LD, IndexNow)이 실제로 적용되어 운영 중인 사례입니다.

- [길라 (Gilra.kr)](https://www.gilra.kr)
- [리얼포춘 (realfortune.co.kr)](https://www.realfortune.co.kr)

---

## License

The MIT License (MIT).
