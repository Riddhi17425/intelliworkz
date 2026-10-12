<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactInquiry extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'company', 'email', 'phone', 'subject', 'message', 'source'];

    protected function casts(): array
    {
        return ['is_read' => 'boolean', 'read_at' => 'datetime'];
    }

    public function scopeUnread(Builder $q): Builder
    {
        return $q->where('is_read', false);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';
        return $q->where(function (Builder $w) use ($like) {
            $w->where('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('company', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('subject', 'like', $like)
                ->orWhere('message', 'like', $like);
        });
    }

    public function markRead(bool $read = true): void
    {
        $this->forceFill(['is_read' => $read, 'read_at' => $read ? now() : null])->save();
    }
}
