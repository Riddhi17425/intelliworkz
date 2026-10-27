<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUSES = [self::STATUS_PUBLISHED => 'Published', self::STATUS_DRAFT => 'Draft'];

    /** slug, template and is_system are set by the importer / controller explicitly, never from a request */
    protected $fillable = [
        'title', 'meta_title', 'meta_description', 'meta_keywords', 'category', 'og_image',
        'featured_image', 'content', 'status', 'published_at', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'layout' => 'array',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // menus cache the linked page (address + published state), so any page change must refresh them
        static::saved(fn () => \App\Support\SiteMenus::flush());
        static::deleted(fn () => \App\Support\SiteMenus::flush());
    }

    /** Admin URLs read /admin/pages/seo-services/edit rather than /admin/pages/7/edit */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Name of the design (Blade view in site/pages) this page is drawn with; null for a plain text page. */
    public function viewKey(): ?string
    {
        if ($this->is_system) {
            return $this->slug;
        }
        return $this->template && !in_array($this->template, ['generic', 'blog-post'], true) ? $this->template : null;
    }

    /** True when the page has a designed layout with its own texts/lists to edit (original pages and copies of them). */
    public function hasEditor(): bool
    {
        return $this->viewKey() !== null;
    }

    /** A blog post: a plain admin page drawn with the blog layout and listed on the blog page. */
    public function isBlog(): bool
    {
        return $this->template === 'blog-post';
    }

    /** True when the page is drawn from sections the admin can hide, reorder and mix with other designs. */
    public function hasSections(): bool
    {
        return \App\Support\SectionPlan::supports($this->viewKey());
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PUBLISHED);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /** Public URL. System pages keep their original *.php address so every existing link still works. */
    public function url(): string
    {
        if ($this->slug === 'index') {
            return url('/');
        }
        return url($this->is_system ? $this->slug . '.php' : $this->slug);
    }

    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }
}
