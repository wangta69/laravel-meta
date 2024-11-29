# Meta manager for laravel

## document



## Installation
```
composer require wangta69/laravel-meta
```

## How to Use
- controller
```
use Pondol\Meta\Facades\Meta;
..........
class SampleController extends Controller
{
  ..........
  public function show(Request $request) {
    $meta = Meta::get();
    return view('show', compact('meta'));
  }
  ..........
}
```
- blade
```
<head>
<x-pondol-meta::meta :meta="$meta"/>
</head>
```

