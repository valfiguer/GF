<?php
class SitemapController {

    public function sitemap(): void {
        $baseUrl  = BASE_URL;
        $articles = ArticleRepository::getLatest(500);

        header('Content-Type: application/xml; charset=utf-8');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Homepage
        echo '  <url><loc>' . e($baseUrl) . '/</loc><changefreq>hourly</changefreq><priority>1.0</priority></url>' . "\n";

        // Category pages
        foreach (['football_eu'] as $sport) {
            echo '  <url><loc>' . e($baseUrl) . '/category/' . $sport . '</loc><changefreq>hourly</changefreq><priority>0.8</priority></url>' . "\n";
        }

        // Live page
        echo '  <url><loc>' . e($baseUrl) . '/live</loc><changefreq>always</changefreq><priority>0.9</priority></url>' . "\n";

        // Articles
        foreach ($articles as $a) {
            $lastmod = $a['updated_at'] ?? $a['created_at'] ?? '';
            $lastmodTag = $lastmod ? '<lastmod>' . substr($lastmod, 0, 10) . '</lastmod>' : '';
            echo '  <url><loc>' . e($baseUrl) . '/article/' . e($a['slug']) . '</loc>' . $lastmodTag . '<priority>0.7</priority></url>' . "\n";
        }

        echo '</urlset>';
        exit;
    }

    public function robots(): void {
        $baseUrl = BASE_URL;
        header('Content-Type: text/plain');
        echo "User-agent: *\nAllow: /\n\nSitemap: {$baseUrl}/sitemap.xml\n";
        exit;
    }
}
