<?php

namespace Pondol\Meta\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use DateTime;

class Meta extends Model
{
    protected $guarded = [];
    protected $fillable = ['name', 'params'];

    /**
    * metas 테이블 데이터를 기반으로 sitemap.xml 파일을 생성합니다.
    *
    * @return int|false 생성된 URL의 개수 또는 실패 시 false
    */
    public static function createSitemap()
    {
        // 1. 사이트맵에 포함할 유효한 URL 데이터를 조회합니다.
        $items = self::whereNotNull('path')->whereNotNull('title')
                     ->orderBy('updated_at', 'desc')
                     ->get();

        // 2. XML 문자열을 만듭니다.
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($items as $item) {
            // URL 경로가 '/'로 시작하지 않으면 붙여줍니다. (안전장치)
            $path = Str::startsWith($item->path, '/') ? $item->path : '/' . $item->path;

            $xml .= '<url>';
            $xml .= '    <loc>' . config('app.url') . $path . '</loc>';
            // lastmod는 W3C Datetime 형식이어야 합니다.
            $xml .= '    <lastmod>' . $item->updated_at->toW3cString() . '</lastmod>';
            $xml .= '    <changefreq>' . ($item->changefreq ?: 'weekly') . '</changefreq>';
            $xml .= '    <priority>' . ($item->priority ?: '0.6') . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        // 3. public 경로에 sitemap.xml 파일을 저장합니다.
        try {
            File::put(public_path('sitemap.xml'), $xml);
            return $items->count(); // 성공 시 처리된 아이템 개수 반환
        } catch (\Exception $e) {
            \Log::error('Sitemap creation failed: ' . $e->getMessage());
            return false; // 실패 시 false 반환
        }
    }
}
