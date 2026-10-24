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

    /**
     * Key => value for the templates. A field switched off in the admin reads as empty; extra paragraphs added in the
     * admin (rows keyed "<field>~<n>") are appended to the field they belong to, so every template shows them in place.
     */
    public function fields(string $scope): array
    {
        return $this->fields[$scope] ??= Cache::rememberForever("content.fields.$scope", function () use ($scope) {
            $map = $types = $extras = $off = [];
            foreach (ContentField::where('scope', $scope)->orderBy('id')->get(['key', 'value', 'type', 'hidden']) as $row) {
                if (str_contains($row->key, '~')) {
                    $extras[explode('~', $row->key, 2)[0]][] = $row;
                    continue;
                }
                $types[$row->key] = $row->type;
                $hide = $row->hidden && $row->type !== 'toggle';
                $map[$row->key] = $hide ? '' : $row->value;
                if ($hide) {
                    $off[$row->key] = true;
                }
            }
            foreach ($extras as $base => $rows) {
                if (!array_key_exists($base, $map) || isset($off[$base])) {
                    continue;                              // unknown field, or switched off together with its extras
                }
                $parts = [];
                foreach ($rows as $row) {
                    if (!$row->hidden && trim((string) $row->value) !== '') {
                        $parts[] = $row->value;
                    }
                }
                if ($parts) {
                    $sep = ($types[$base] ?? 'text') === 'text' ? '<br>' : '<br><br>';
                    $map[$base] = trim((string) $map[$base]) === '' ? implode($sep, $parts) : $map[$base] . $sep . implode($sep, $parts);
                }
            }
            return $map;
        });
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
