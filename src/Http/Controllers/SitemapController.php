<?php

namespace Pondol\Meta\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;

use Pondol\Meta\Models\Meta;
use App\Http\Controllers\Controller;

class SitemapController extends Controller
{
  //
  public function __construct(
  ){
  }

  public function index(Request $request) {
    // $items = Meta::whereNotNull('path')->whereNotNull('title')->orderBy('updated_at', 'desc')->get();
    $items = cache()->remember('sitemap_items', 3600, function() {
        return Meta::whereNotNull('path')
               ->whereNotNull('title')
               ->orderBy('updated_at', 'desc')
               ->get();
    });

    return response()->view('pondol-meta::sitemap', compact('items'))
      ->header('Content-Type', 'text/xml')
      ->header('Cache-Control', 'public, max-age=3600');
  }
}
