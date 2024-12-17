<?php echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($items as $item)
  <url>
    <loc>{{ config('app.url') }}/{{ $item->path }}</loc>
    <lastmod>{{ $item->updated_at->tz('UTC')->toAtomString() }}</lastmod>
    <changefreq>{{ $item->changefreq }}</changefreq>
    <priority>{{ $item->priority }}</priority>
  </url>
@endforeach
</urlset>