<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class SeoHelper
{
    /**
     * Generate meta description dari konten.
     */
    public static function metaDescription(?string $content, ?int $length = null): string
    {
        $length = $length ?? config('seo.meta_desc_length', 160);

        if (empty($content)) {
            return config('seo.description', '');
        }

        $plain = strip_tags($content);
        $plain = preg_replace('/\s+/', ' ', $plain);

        return Str::limit(trim($plain), $length, '...');
    }

    /**
     * Generate canonical URL.
     */
    public static function canonicalUrl(?string $path = null): string
    {
        $base = rtrim(config('app.url'), '/');

        if ($path) {
            return $base . '/' . ltrim($path, '/');
        }

        return $base . request()->getPathInfo();
    }

    /**
     * Generate Open Graph image URL.
     */
    public static function ogImage(?string $imagePath = null): string
    {
        if ($imagePath) {
            $storagePath = config('seo.og_storage_path', 'storage/');

            return str_starts_with($imagePath, 'http')
                ? $imagePath
                : asset($storagePath . ltrim($imagePath, '/'));
        }

        return config('seo.default_image', asset('images/og-default.jpg'));
    }

    /**
     * Generate keywords dari tags dan kategori.
     */
    public static function keywords(array $tags = [], ?string $category = null): string
    {
        $keywords = config('seo.keywords', []);

        if ($category) {
            $keywords[] = $category;
        }

        foreach ($tags as $tag) {
            if (is_object($tag) && isset($tag->name)) {
                $keywords[] = $tag->name;
            } elseif (is_array($tag) && isset($tag['name'])) {
                $keywords[] = $tag['name'];
            } elseif (is_string($tag)) {
                $keywords[] = $tag;
            }
        }

        return implode(', ', array_unique($keywords));
    }

    /**
     * Truncate text dengan tetap menjaga kata utuh.
     */
    public static function truncateText(?string $text, int $length = 160): string
    {
        if (empty($text)) {
            return '';
        }

        return Str::limit(strip_tags($text), $length, '...');
    }

    /**
     * Generate SEO array untuk halaman list/statik dari config.
     */
    public static function pageSeo(string $pageKey, ?string $ogType = 'website', ?string $ogImage = null): array
    {
        $pageConfig = config("seo.pages.{$pageKey}");

        return [
            'title' => ($pageConfig['title'] ?? $pageKey) . ' — ' . config('seo.site_name'),
            'description' => $pageConfig['description'] ?? config('seo.description'),
            'keywords' => $pageConfig['keywords'] ?? implode(', ', config('seo.keywords', [])),
            'og_type' => $ogType,
            'og_image' => $ogImage ?? config('seo.default_image'),
        ];
    }
}
