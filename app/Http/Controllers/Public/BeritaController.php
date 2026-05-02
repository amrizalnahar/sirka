<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class BeritaController extends Controller
{
    public function index()
    {
        $posts = Post::with(['category', 'tags', 'author'])
            ->published()
            ->latest('published_at')
            ->get();

        $categories = Category::byModule('post')->whereHas('posts', fn ($q) => $q->published())->get();

        $years = Post::published()
            ->selectRaw('DISTINCT ' . \App\Helpers\DatabaseHelper::year('published_at') . ' as year')
            ->pluck('year')
            ->sortDesc()
            ->values();

        $featured = Post::with(['category', 'tags', 'author'])
            ->published()
            ->latest('published_at')
            ->first();

        $seo = \App\Helpers\SeoHelper::pageSeo('berita');

        return view('pages.berita.index', compact('posts', 'categories', 'featured', 'years', 'seo'));
    }

    public function show(string $slug)
    {
        $post = Post::with(['category', 'tags', 'author'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $post->increment('views');

        $seo = [
            'title' => $post->seo_title . ' — Berita',
            'description' => $post->seo_description,
            'keywords' => $post->seo_keywords,
            'og_type' => 'article',
            'og_image' => \App\Helpers\SeoHelper::ogImage($post->thumbnail),
            'canonical_url' => route('berita.show', $post->slug),
            'meta_author' => $post->author?->name ?? config('seo.author'),
        ];

        $relatedPosts = Post::whereHas('tags', function ($q) use ($post) {
            $q->whereIn('tags.id', $post->tags->pluck('id'));
        })
            ->where('id', '!=', $post->id)
            ->published()
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('pages.berita.show', compact('post', 'relatedPosts', 'seo'));
    }
}
