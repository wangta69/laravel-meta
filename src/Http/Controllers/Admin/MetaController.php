<?php

namespace Pondol\Meta\Http\Controllers\Admin;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;

use Pondol\Meta\Models\Meta;
use App\Http\Controllers\Controller;

class MetaController extends Controller
{

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
  }
  /**
   * 회원가입시 이메일 유효성 및 중복 체크
   */
  public function index(Request $request) {
    $sk = $request->sk;
    $sv = $request->sv;
    $from_date = $request->from_date;
    $to_date = $request->to_date;

    $items = Meta::select('*');

    if ($sv) {
      $items = $items->where($sk, 'like', '%' . $sv . '%');
    }

    if ($from_date) {
      if (!$to_date) {
        $to_date = date("Y-m-d");
      }

      $from_date = Carbon::createFromFormat('Y-m-d', $from_date);
      $to_date = Carbon::createFromFormat('Y-m-d', $to_date);
      $items =  $items->whereBetween('metas.created_at', [$from_date->startOfDay(), $to_date->endOfDay()]);
    }


    $items = $items->orderBy('id', 'desc')->paginate(20)->appends(request()->query());


    return view('pondol-meta::admin.index', compact('items'));
  }

  public function edit(Request $request, Meta $item) {
    return view('pondol-meta::admin.edit', compact('item'));
  }

  public function update(Request $request, Meta $item) {
    $item->title = $request->title;
    $item->keywords = $request->keywords;
    $item->image = $request->image;
    $item->description = $request->description;
    $item->save();

    return redirect()->route('meta.admin.index');
  }

  public function destory(Request $request, Meta $item) {
    $item->delete();
    if($request->ajax()){
      return response()->json(['error'=>false], 200); // 500, 203
    } else {
      return redirect()->route('meta.admin.index');
    }

    
  }
}
