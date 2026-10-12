<?php

namespace App\Content;

use App\Support\Html;
use App\Support\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Describes how one repeatable list (see config/content_lists.php) is edited, and turns the submitted form back
 * into the exact array shape the page template expects. Anything the editor does not expose (colours, pixel
 * positions, icons …) is carried over from the stored item, never from the request.
 */
class ListSchema
{
    public const KINDS = ['strings', 'rows', 'map', 'maprows', 'groups'];

    private ?array $fields = null;

    public function __construct(public readonly string $key, private readonly array $meta, private readonly array $items) {}

    public static function make(string $key, array $items): self
    {
        return new self($key, config('content_lists.' . $key, []), $items);
    }

    /* ------------------------------------------------------------------ description */

    public function hidden(): bool
    {
        return (bool) ($this->meta['hidden'] ?? false);
    }

    public function label(): string
    {
        return $this->meta['label'] ?? Str::headline($this->key);
    }

    public function kind(): string
    {
        if (isset($this->meta['kind'])) {
            return $this->meta['kind'];
        }
        $first = Arr::first($this->items);
        if (!array_is_list($this->items)) {
            return is_array($first) ? 'maprows' : 'map';
        }
        if (is_string($first) || $first === null) {
            return 'strings';
        }
        if (is_array($first) && is_array(Arr::first($first)) && array_is_list($first)) {
            return 'groups';
        }
        return 'rows';
    }

    public function canAdd(): bool
    {
        return in_array($this->kind(), ['strings', 'rows'], true) && ($this->meta['add'] ?? true);
    }

    public function canRemove(): bool
    {
        return in_array($this->kind(), ['strings', 'rows'], true) && ($this->meta['remove'] ?? true);
    }

    public function canReorder(): bool
    {
        return in_array($this->kind(), ['strings', 'rows'], true) && ($this->meta['reorder'] ?? $this->canAdd());
    }

    public function groupLabel(): string
    {
        return $this->meta['group'] ?? 'Group';
    }

    /** @return array<int|string, array{label:string,type:string,help:?string,fields:array,wrap:bool}> */
    public function fields(): array
    {
        return $this->fields ??= $this->normalize($this->meta['fields'] ?? $this->infer());
    }

    /** Heading for a collapsed item card. */
    public function itemTitle(mixed $item, int|string $index): string
    {
        $key = $this->meta['title'] ?? array_key_first($this->fields());
        $value = is_array($item) ? ($item[$key] ?? '') : $item;
        $text = Html::plain(is_array($value) ? implode(', ', Arr::flatten($value)) : (string) $value);
        return $text !== '' ? Str::limit($text, 70) : (($this->kind() === 'groups' ? $this->groupLabel() : 'Item') . ' ' . (is_int($index) ? $index + 1 : $index));
    }

    /** A new row starts as a copy of the last one, with every editable value cleared (hidden design values stay). */
    public function blank(): array
    {
        $template = is_array(Arr::last($this->items)) ? Arr::last($this->items) : [];
        foreach ($this->fields() as $key => $def) {
            $template[$key] = in_array($def['type'], ['lines', 'repeater'], true) ? [] : '';
        }
        if (array_is_list($template)) {
            ksort($template);
        }
        return $template;
    }

    /* ------------------------------------------------------------------ saving */

    /**
     * @param array $post  the submitted "lists[id]" array
     * @param array $files uploaded files for the same prefix (same nesting)
     */
    public function apply(array $post, array $files): array
    {
        return match ($this->kind()) {
            'strings' => $this->applyStrings($post),
            'map' => $this->applyMap($post),
            'maprows' => $this->applyMapRows($post, $files),
            'groups' => $this->applyGroups($post, $files),
            default => $this->applyRows($post, $files),
        };
    }

    private function applyStrings(array $post): array
    {
        $out = [];
        foreach ((array) ($post['items'] ?? []) as $value) {
            $clean = $this->text(is_string($value) ? $value : '', $this->items);
            if ($clean !== '') {
                $out[] = $clean;
            }
        }
        // a fixed-length list keeps its original length
        if (!$this->canAdd() && !$this->canRemove()) {
            foreach ($this->items as $i => $orig) {
                $out[$i] = $out[$i] ?? $orig;
            }
            ksort($out);
        }
        return array_values($out);
    }

    private function applyMap(array $post): array
    {
        $out = [];
        foreach ($this->items as $key => $orig) {
            $v = $post['map'][$key] ?? null;
            $clean = is_string($v) ? $this->text($v, $orig) : '';
            $out[$key] = $clean !== '' ? $clean : $orig;
        }
        return $out;
    }

    private function applyMapRows(array $post, array $files): array
    {
        $out = $this->items;
        foreach ($this->items as $key => $orig) {
            $out[$key] = $this->fillRow($orig, (array) ($post['map'][$key] ?? []), (array) ($files['map'][$key] ?? []));
        }
        return $out;
    }

    private function applyGroups(array $post, array $files): array
    {
        $out = $this->items;
        foreach ($this->items as $g => $rows) {
            foreach ($rows as $r => $orig) {
                $out[$g][$r] = $this->fillRow($orig, (array) ($post['groups'][$g][$r] ?? []), (array) ($files['groups'][$g][$r] ?? []));
            }
        }
        return $out;
    }

    private function applyRows(array $post, array $files): array
    {
        $posted = (array) ($post['items'] ?? []);
        $filesItems = (array) ($files['items'] ?? []);
        $fixed = !$this->canAdd() && !$this->canRemove();
        $out = [];
        $seen = [];

        foreach ($posted as $n => $row) {
            if (!is_array($row)) {
                continue;
            }
            $orig = $row['_orig'] ?? '';
            $hasOrig = $orig !== '' && ctype_digit((string) $orig) && isset($this->items[(int) $orig]);
            if (!$hasOrig && (!$this->canAdd() || $fixed)) {
                continue;                       // new rows are not allowed for this list
            }
            if (!$hasOrig && ((string) $n === '__N__' || !preg_match('/^[A-Za-z0-9]+$/', (string) $n))) {
                continue;                       // the hidden "add row" prototype (key __N__) is never a real row
            }
            $base = $hasOrig ? $this->items[(int) $orig] : $this->blank();
            $filled = $this->fillRow($base, $row, (array) ($filesItems[$n] ?? []));
            if (!$hasOrig && $this->isBlank($filled)) {
                continue;                       // "Add" was clicked but nothing was typed
            }
            if ($hasOrig) {
                $seen[(int) $orig] = true;
            }
            $out[] = $filled;
        }

        // rows that may not be deleted come back untouched
        if (!$this->canRemove()) {
            foreach ($this->items as $i => $orig) {
                if (!isset($seen[$i])) {
                    $out[] = $orig;
                }
            }
        }

        // a fixed list also keeps its original order
        if ($fixed) {
            $byOrig = [];
            foreach ($posted as $n => $row) {
                if (is_array($row) && isset($row['_orig']) && ctype_digit((string) $row['_orig'])) {
                    $byOrig[(int) $row['_orig']] = $this->fillRow($this->items[(int) $row['_orig']] ?? [], $row, (array) ($filesItems[$n] ?? []));
                }
            }
            $out = [];
            foreach ($this->items as $i => $orig) {
                $out[] = $byOrig[$i] ?? $orig;
            }
        }
        return array_values($out);
    }

    /** True when none of the editable fields has a value (hidden design values copied by blank() do not count). */
    private function isBlank(array $row): bool
    {
        foreach (array_keys($this->fields()) as $key) {
            $v = $row[$key] ?? '';
            if ($v !== '' && $v !== [] && $v !== null) {
                return false;
            }
        }
        return true;
    }

    /** Apply the editable fields of one row onto its stored version. */
    private function fillRow(array $base, array $input, array $files, ?array $defs = null): array
    {
        foreach ($defs ?? $this->fields() as $key => $def) {
            if (!array_key_exists($key, $input) && !isset($files[$key . '__file']) && empty($input[$key . '__remove'])) {
                continue;     // field was not on the form: keep it
            }
            $value = $this->cast($def, $input[$key] ?? null, $input, $files[$key . '__file'] ?? null, $base[$key] ?? null, (string) $key);
            if (!array_key_exists($key, $base) && ($value === '' || $value === [])) {
                continue;     // an optional value this row never had stays absent
            }
            $base[$key] = $value;
        }
        // a positional row (['Question', 'Answer']) stays a plain list in its original order
        if ($base && !array_filter(array_keys($base), 'is_string')) {
            ksort($base);
            $base = array_values($base);
        }
        return $base;
    }

    private function cast(array $def, mixed $value, array $row, mixed $file, mixed $current, string $key): mixed
    {
        switch ($def['type']) {
            case 'image':
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $this->assertImage($file, $def['label']);
                    Media::delete(is_string($current) ? $current : null);
                    return Media::store($file, 'images');
                }
                if (!empty($row[$key . '__remove'])) {
                    Media::delete(is_string($current) ? $current : null);
                    return '';
                }
                return is_string($current) ? $current : '';

            case 'url':
                $url = trim((string) $value);
                return ($url === '' || Html::safeUrl($url)) ? mb_substr($url, 0, 500) : (is_string($current) ? $current : '');

            case 'lines':
                $lines = is_string($value) ? preg_split('/\R/', $value) : (array) $value;
                return array_values(array_filter(array_map(fn ($l) => $this->text((string) $l, $current), $lines), fn ($l) => $l !== ''));

            case 'repeater':
                return $this->castRepeater($def, (array) $value, $current);

            case 'html':
            case 'textarea':
            case 'text':
            default:
                return $this->text(is_string($value) ? $value : '', $current);
        }
    }

    /**
     * Clean typed text for storing. Text that is exactly what the box showed for an existing value keeps that value
     * byte for byte, so saving an untouched form never re-encodes the original copy.
     * @param mixed $originals a stored string, or any nesting of stored strings
     */
    private function text(string $posted, mixed $originals): string
    {
        $norm = fn (string $s) => trim(str_replace("
", "
", $s));
        $posted = $norm($posted);
        foreach (is_array($originals) ? Arr::flatten($originals) : [$originals] as $orig) {
            if (is_string($orig) && $orig !== '' && $posted === $norm(Html::forInput($orig))) {
                return $orig;
            }
        }
        return Html::clean($posted);
    }

    private function castRepeater(array $def, array $rows, mixed $current = null): array
    {
        $out = [];
        $positional = array_filter(array_keys($def['fields']), 'is_int') === array_keys($def['fields']);
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $item = [];
            $empty = true;
            foreach ($def['fields'] as $k => $sub) {
                $v = $this->cast($sub, $row[$k] ?? '', $row, null, $current, (string) $k);
                $item[$k] = $v;
                $empty = $empty && ($v === '' || $v === []);
            }
            if ($empty) {
                continue;
            }
            if ($positional) {
                ksort($item);
                $item = array_values($item);
            }
            $out[] = !empty($def['wrap']) ? [$item] : $item;
        }
        return $out;
    }

    private function assertImage(UploadedFile $file, string $label): void
    {
        $v = Validator::make(['f' => $file], ['f' => Media::rule(false)], [], ['f' => $label]);
        if ($v->fails()) {
            throw ValidationException::withMessages(['content' => $v->errors()->first('f')]);
        }
    }

    /* ------------------------------------------------------------------ helpers */

    /** @return array<int|string, array> */
    private function normalize(array $specs): array
    {
        $out = [];
        foreach ($specs as $key => $spec) {
            $out[$key] = [
                'label' => $spec[0] ?? Str::headline((string) $key),
                'type' => $spec[1] ?? 'text',
                'help' => $spec['help'] ?? null,
                'wrap' => (bool) ($spec['wrap'] ?? false),
                'fields' => isset($spec['fields']) ? $this->normalize($spec['fields']) : [],
            ];
        }
        return $out;
    }

    /** Fallback for a list without an entry in config/content_lists.php. */
    private function infer(): array
    {
        $sample = [];
        foreach ($this->kind() === 'maprows' ? $this->items : array_slice($this->items, 0, 8) as $item) {
            if (is_array($item)) {
                foreach ($item as $k => $v) {
                    $sample[$k] ??= $v;
                }
            }
        }
        $specs = [];
        foreach ($sample as $k => $v) {
            if (is_array($v) && collect($v)->every(fn ($x) => is_string($x))) {
                $specs[$k] = [Str::headline((string) $k), 'lines'];
            } elseif (is_string($v) && !preg_match('/^(var\(|#[0-9a-f]{3,8}$|rgba?\(|linear-gradient)/i', $v) && !in_array($k, ['no', 'c', 'noc', 'noa', 'color', 'tint', 'bg', 'x', 'y', 'w', 'h'], true)) {
                $specs[$k] = [Str::headline((string) $k), preg_match('/\.(png|jpe?g|webp|gif)$/i', $v) ? 'image' : (mb_strlen($v) > 110 ? 'textarea' : 'text')];
            }
        }
        return $specs;
    }
}
