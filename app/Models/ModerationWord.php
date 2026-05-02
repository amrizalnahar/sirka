<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModerationWord extends Model
{
    protected $fillable = [
        'word',
        'category',
        'severity',
        'is_regex',
        'is_active',
    ];

    protected $casts = [
        'is_regex' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBlockLevel($query)
    {
        return $query->whereIn('severity', ['medium', 'high']);
    }
}
