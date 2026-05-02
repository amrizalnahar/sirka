<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module_type',
        'name',
        'slug',
        'description',
    ];

    protected $casts = [
        'module_type' => 'string',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('module_type', $module);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('slug', 'like', "%{$term}%");
        });
    }
}
