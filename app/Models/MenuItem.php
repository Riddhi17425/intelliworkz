<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    protected $fillable = ['label', 'url', 'page_id', 'description', 'sort', 'is_published', 'new_tab'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'new_tab' => 'boolean'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(MenuGroup::class, 'menu_group_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** A linked CMS page wins over the typed URL, and an unpublished page falls back to "#". */
    public function href(): string
    {
        if ($this->page_id && $this->page) {
            return $this->page->isPublished() ? $this->page->url() : '#';
        }
        return $this->url ?: '#';
    }
}
