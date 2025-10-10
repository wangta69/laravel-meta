<?php echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($items as $item)
    <url>
        <loc>{{ url(str_starts_with($item->path, '/') ? $item->path : '/' . $item->path) }}</loc>
        <lastmod>{{ \Carbon\Carbon::parse($item->updated_at)->toAtomString() }}</lastmod>
@if($item->changefreq)
        <changefreq>{{ $item->changefreq }}</changefreq>
@endif
@if($item->priority)
        <priority>{{ $item->priority }}</priority>
@endif
    </url>
@endforeach
</urlset>