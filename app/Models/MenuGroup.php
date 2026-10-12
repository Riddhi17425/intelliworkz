<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuGroup extends Model
{
    public const LOCATIONS = ['header' => 'Services menu (header)', 'footer' => 'Footer columns'];

    protected $fillable = ['title', 'sort', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort')->orderBy('id');
    }

    public function scopeAt(Builder $q, string $location): Builder
    {
        return $q->where('location', $location)->orderBy('sort')->orderBy('id');
    }
}
