<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'description', 'ip_address'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** One-line audit entry for the dashboard "Recent activity" feed. */
    public static function record(string $action, string $description, ?Model $subject = null): void
    {
        static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'description' => mb_substr($description, 0, 500),
            'ip_address' => request()?->ip(),
        ]);
    }

    public function icon(): string
    {
        return match ($this->action) {
            'created' => 'bi-plus-circle',
            'deleted' => 'bi-trash3',
            'login' => 'bi-box-arrow-in-right',
            'logout' => 'bi-box-arrow-right',
            'inquiry' => 'bi-envelope',
            default => 'bi-pencil-square',
        };
    }
}
