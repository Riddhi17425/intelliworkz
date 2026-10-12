<?php

namespace App\Support;

use App\Models\ContentField;
use App\Models\ContentList;
use Illuminate\Support\Facades\Cache;

/**
 * Reads page copy (content_fields) and repeatable lists (content_lists) for one scope
 * (a page slug or "global"). Results are cached until the admin saves that scope again.
 */
class Content
{
    /** @var array<string, array<string, string|null>> */
    private array $fields = [];

    /** @var array<string, array<string, array>> */
    private array $lists = [];

    public function fields(string $scope): array
    {
        return $this->fields[$scope] ??= Cache::rememberForever(
            "content.fields.$scope",
            fn () => ContentField::where('scope', $scope)->pluck('value', 'key')->all()
        );
    }

    public function lists(string $scope): array
    {
        return $this->lists[$scope] ??= Cache::rememberForever(
            "content.lists.$scope",
            fn () => ContentList::where('scope', $scope)->get(['key', 'items'])->mapWithKeys(fn ($l) => [$l->key => $l->items ?? []])->all()
        );
    }

    public function field(string $scope, string $key, string $default = ''): string
    {
        $value = $this->fields($scope)[$key] ?? null;
        return $value === null ? $default : (string) $value;
    }

    public function list(string $scope, string $key): array
    {
        return $this->lists($scope)[$key] ?? [];
    }

    public static function forget(string $scope): void
    {
        Cache::forget("content.fields.$scope");
        Cache::forget("content.lists.$scope");
        app(self::class)->reset($scope);
    }

    public function reset(string $scope): void
    {
        unset($this->fields[$scope], $this->lists[$scope]);
    }
}
