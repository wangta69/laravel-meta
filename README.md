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
### Meta::get()
> Meta::get() 을 사용하면 현재 동일 라우터 명과 파라미터에 대해서 데이타를 가져오지만 없을 경우 새롭게 추가를 합니다. <br>
> 따라서 laravel meta 에서 제공하는 관리자 모드로 접근하여 관련 메타 정보를 변경가능합니다. <br>
> 먼저 config/pondol-meta.php에서 접근권한을 변경한 후 아래처럼 typing하시면 관리자 모드로 접근 하실 수 있습니다. <br>
```
yourDomain/meta/admin
```


