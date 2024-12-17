# Meta manager for laravel
> 라라벨용 html meta tag 관리 프로그램입니다.

## Installation
```
composer require wangta69/laravel-meta
php artisan pondol:install-meta
```

## How to Use

### 메타 생성
#### database를 생성할때 자동생성
```
Meta::set("Route Name", ["Route Parameters"])->update();
```
- controller
```
use Pondol\Meta\Facades\Meta;
..........
class SampleController extends Controller
{
  ..........
  public function store(Request $request) {
    ..........
    Meta::set('market.item', ['item'=>(string)$item->id])
    ->title($item->name)
    ->description($item->shorten_description)
    ->extractKeywordsFromArray($item->tags, 'tag')
    ->image(\Storage::url($item->image))
    ->update();

  }
  ..........
}
```
### 메타 가져오기
#### 기존에 생성된 메타 가져오기
- controller
```
use Pondol\Meta\Facades\Meta;
..........
class ViewerController extends Controller
{
  ..........
  public function store(Request $request) {
    ..........
    $meta = Meta::get(); // 현재의 route name 및 parameter를 이용하여 자동으로 가져옮

    return view('view-blade', ['meta'=>$meta]);
  }
  ..........
}
```
- blade
```
<x-pondol-meta::meta :meta="$meta"/>
```
#### 실시간으로 메타 생성하기
> 검색결과 등을 메타로 처리할 경우등에 사용할 수 있습니다. <br>
- controller
```
use Pondol\Meta\Facades\Meta;
..........
class ViewerController extends Controller
{
  ..........
  public function store(Request $request) {
    ..........
    $meta = Meta::get()
    ->title($request->q)
    ->description($request->q.'에 대한 검색결과');

    return view('view-blade', ['meta'=>$meta]);
  }
  ..........
}
```

#### 기존 메타에 내용 추가하기
```
 $meta = Meta::get()
  ->suffix(function($suffix) use($page){
    $suffix->title = ' '.$page.'page';
  });
```
### Meta::get()
> Meta::get() 을 사용하면 현재 동일 라우터 명과 파라미터에 대해서 데이타를 가져오지만 없을 경우 새롭게 추가를 합니다. <br>
> 따라서 laravel meta 에서 제공하는 관리자 모드로 접근하여 관련 메타 정보를 변경가능합니다. <br>
> 먼저 config/pondol-meta.php에서 접근권한을 변경한 후 아래처럼 typing하시면 관리자 모드로 접근 하실 수 있습니다. <br>
```
yourDomain/meta/admin
```

### og:image
> og:image는 이미 존재하는 이미지를 가져오는 방식과 실시간으로 이미지를 생성하는 방식 두가지를 제공합니다.
#### 기존 이미지를 넣기
```
Meta::get()->image(Put image path);
```

#### 실시간 이미지 생성
> $image->create(); 사용시 config/pondol-meta.php에서 정의된 dummy_image 의 정보를 이용하여 백그라운드가 존재하는 텍스트 이미지를 생성합니다.
```
$meta = Meta::get()->create_image(function($image){
  $image->create();
});
```
> config 파일외에 실시간으로 변경하고자 하면 아래처럼 변경하고자 하는 값을 넣어 주시면 됩니다.
```
$meta = Meta::get()->create_image(function($image){
  $image->save_path = '';
  $image->background_image = '';
  $image->font = '';
  $image->fontSize = '';

  $image->create();
});
```

## SiteMap
> Pondol Meta 는 기본적으로 google 등을 위한 사이트 맵도 기본 적으로 제공합니다. <br>
> 기본 제공 경로를 바꾸시려면 config/pondol-meta.php에서 'route_sitemap.prefix' 속성을 변경하시면 됩니다.
```
YourDomain/meta/{vendor}.xml

YourDomain/meta/google.xml
YourDomain/meta/naver.xml
```

