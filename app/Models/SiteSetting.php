<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    public const GROUPS = [
        'company' => 'Company',
        'branding' => 'Logo & branding',
        'contact' => 'Contact details',
        'social' => 'Social media',
        'seo' => 'Default SEO',
    ];

    protected $fillable = ['value'];

    private const CACHE_KEY = 'site_settings.all';

    /** All settings as key => value (cached; flushed whenever a setting is saved). */
    public static function map(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $v = static::map()[$key] ?? null;
        return ($v === null || $v === '') ? $default : $v;
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flush());
        static::deleted(fn () => static::flush());
    }
}
