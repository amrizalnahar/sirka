<?php

namespace App\Models;

use App\Traits\HasAuditTrail;
use App\Traits\HasCategory;
use App\Traits\HasSlug;
use App\Traits\HasTags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasAuditTrail, HasCategory, HasFactory, HasSlug, HasTags, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'category_id',
        'thumbnail',
        'status',
        'published_at',
        'author_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'status' => 'string',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('content', 'like', "%{$term}%");
        });
    }

    /**
     * Resolved SEO title dengan fallback ke title.
     */
    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?? $this->title;
    }

    /**
     * Resolved SEO description dengan fallback ke content.
     */
    public function getSeoDescriptionAttribute(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }

        return \App\Helpers\SeoHelper::metaDescription($this->content);
    }

    /**
     * Resolved SEO keywords dengan fallback ke tags + category.
     */
    public function getSeoKeywordsAttribute(): string
    {
        if ($this->meta_keywords) {
            return $this->meta_keywords;
        }

        return \App\Helpers\SeoHelper::keywords($this->tags->toArray(), $this->category?->name);
    }
}
