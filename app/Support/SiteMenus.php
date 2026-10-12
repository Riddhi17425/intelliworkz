<?php

namespace App\Support;

use App\Models\MenuGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/** Published header groups / footer columns for the public site (cached until a menu is edited in the admin). */
class SiteMenus
{
    private const KEY = 'site_menus.v1';

    /** @return array{header: Collection, footer: Collection} footer is keyed by handle */
    public static function get(): array
    {
        return Cache::rememberForever(self::KEY, function () {
            $load = fn (string $location) => MenuGroup::at($location)->where('is_published', true)
                ->with(['items' => fn ($q) => $q->where('is_published', true)->with('page')])->get();

            return ['header' => $load('header'), 'footer' => $load('footer')->keyBy('handle')];
        });
    }

    public static function flush(): void
    {
        Cache::forget(self::KEY);
    }
}
