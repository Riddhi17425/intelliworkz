<?php

namespace App\Content;

use App\Support\Html;
use App\Support\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

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
        return ($this->meta['add'] ?? true) === true;
    }

    /** The most items the design of the section has room for (null = no limit). Only new rows are held back; existing ones stay. */
    public function maxItems(): ?int
    {
        return isset($this->meta['max']) ? (int) $this->meta['max'] : null;
    }

    public function canRemove(): bool
    {
        return ($this->meta['remove'] ?? true) === true;
    }

    public function canReorder(): bool
    {
        return ($this->meta['reorder'] ?? $this->canAdd()) === true;
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
    public function blank(?array $like = null): array
    {
        $last = $like ?? Arr::last($this->items);
        if ($like === null && $this->kind() === 'groups') {
            $last = is_array($last) ? Arr::last($last) : null;     // a new tool copies the last tool of the last column
        }
        $template = is_array($last) ? $last : [];
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
        // the section has room for only so many; a list that is already longer keeps the length it has
        if ($this->maxItems() !== null) {
            $out = array_slice($out, 0, max($this->maxItems(), count($this->items)));
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
        foreach ((array) ($post['map'] ?? []) as $key => $v) {
            if (!array_key_exists($key, $this->items)) {
                continue;
            }
            $clean = is_string($v) ? $this->text($v, $this->items[$key]) : '';
            $out[$key] = $clean !== '' ? $clean : $this->items[$key];
        }
        if ($this->canAdd()) {
            foreach ((array) ($post['new'] ?? []) as $n => $v) {
                $clean = is_string($v) && (string) $n !== '__N__' ? $this->text($v, '') : '';
                if ($clean !== '') {
                    $out[$this->newKey($clean, $out)] = $clean;
                }
            }
        }
        return $this->keepUnremovable($out);
    }

    /** Rows the editor may not delete come back (at their old place in the order when possible). */
    private function keepUnremovable(array $out): array
    {
        foreach ($this->items as $key => $orig) {
            if (!array_key_exists($key, $out) && !$this->canRemove()) {
                $out = $this->insertAt($out, $key, $orig);
            }
        }
        return $out;
    }

    private function insertAt(array $out, int|string $key, mixed $value): array
    {
        $pos = (int) array_search($key, array_keys($this->items), true);
        return array_slice($out, 0, $pos, true) + [$key => $value] + array_slice($out, $pos, null, true);
    }

    /** A unique key for a new map entry, made from its name ("Food & Drink" -> food-drink). */
    private function newKey(string $name, array $taken): string
    {
        $base = Str::slug(Html::plain($name)) ?: 'item';
        $key = $base;
        for ($i = 2; array_key_exists($key, $taken) || array_key_exists($key, $this->items); $i++) {
            $key = $base . '-' . $i;
        }
        return $key;
    }

    private function applyMapRows(array $post, array $files): array
    {
        $out = [];
        foreach ((array) ($post['map'] ?? []) as $key => $row) {
            if (array_key_exists($key, $this->items) && is_array($row)) {
                $out[$key] = $this->fillRow($this->items[$key], $row, (array) ($files['map'][$key] ?? []));
            }
        }
        if ($this->canAdd()) {
            foreach ((array) ($post['new'] ?? []) as $n => $row) {
                if (!is_array($row) || (string) $n === '__N__') {
                    continue;
                }
                $filled = $this->fillRow($this->blank(), $row, (array) ($files['new'][$n] ?? []));
                if (!$this->isBlank($filled)) {
                    $name = Html::plain((string) ($filled[$this->meta['title'] ?? ''] ?? ''));
                    $out[$this->newKey($name !== '' ? $name : 'office', $out)] = $filled;
                }
            }
        }
        return $this->keepUnremovable($out);
    }

    private function applyGroups(array $post, array $files): array
    {
        if (!$this->canAdd() && !$this->canRemove()) {
            $out = $this->items;
            foreach ($this->items as $g => $rows) {
                foreach ($rows as $r => $orig) {
                    $out[$g][$r] = $this->fillRow($orig, (array) ($post['groups'][$g][$r] ?? []), (array) ($files['groups'][$g][$r] ?? []));
                }
            }
            return $out;
        }

        $out = [];
        foreach ((array) ($post['groups'] ?? []) as $g => $rows) {
            if (!is_array($rows) || (string) $g === '__G__') {
                continue;
            }
            $column = [];
            foreach ($rows as $r => $row) {
                if (!is_array($row) || (string) $r === '__M__') {
                    continue;
                }
                [$og, $or] = array_pad(explode('.', (string) ($row['_orig'] ?? ''), 2), 2, '');
                $orig = ctype_digit($og) && ctype_digit($or) ? ($this->items[(int) $og][(int) $or] ?? null) : null;
                if ($orig === null && !$this->canAdd()) {
                    continue;
                }
                $filled = $this->fillRow($orig ?? $this->blank(Arr::last($column) ?: null), $row, (array) ($files['groups'][$g][$r] ?? []));
                if ($orig === null && $this->isBlank($filled)) {
                    continue;                       // "Add" was clicked but nothing was typed
                }
                $column[] = $filled;
            }
            if ($column) {
                $out[] = $column;
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
            if (!$hasOrig && $this->maxItems() !== null && count($out) >= $this->maxItems()) {
                continue;                       // the section has no room for more items
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
            $file = $def['type'] === 'repeater' ? (array) ($files[$key] ?? []) : ($files[$key . '__file'] ?? null);
            $value = $this->cast($def, $input[$key] ?? null, $input, $file, $base[$key] ?? null, (string) $key);
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
                    try {
                        $path = Media::store($file, 'images');
                    } catch (RuntimeException $e) {
                        throw ValidationException::withMessages(['content' => $def['label'] . ': ' . $e->getMessage()]);
                    }
                    Media::delete(is_string($current) ? $current : null);
                    return $path;
                }
                if (!empty($row[$key . '__remove'])) {
                    Media::delete(is_string($current) ? $current : null);
                    return '';
                }
                return is_string($current) ? $current : '';

            case 'video':
                // either an uploaded file (stored in uploads/videos) or a link (YouTube, Vimeo or a direct .mp4 address)
                $old = is_string($current) ? $current : '';
                if ($file instanceof UploadedFile) {
                    if (!$file->isValid()) {
                        throw ValidationException::withMessages(['content' => $def['label'] . ': the video could not be uploaded. It is probably bigger than the server allows (' . ini_get('upload_max_filesize') . '). Use a smaller file or a YouTube link.']);
                    }
                    if (Media::videoExtension($file) === null) {
                        throw ValidationException::withMessages(['content' => $def['label'] . ': “' . $file->getClientOriginalName() . '” is not a video this page can play (it was detected as ' . ($file->getMimeType() ?: 'unknown') . '). Upload an MP4, WebM or MOV file, or paste a YouTube link.']);
                    }
                    if ($file->getSize() > Media::VIDEO_MAX_KB * 1024) {
                        throw ValidationException::withMessages(['content' => $def['label'] . ': “' . $file->getClientOriginalName() . '” is ' . round($file->getSize() / 1048576) . ' MB, the limit is ' . (Media::VIDEO_MAX_KB / 1024) . ' MB. Use a smaller file or a YouTube link.']);
                    }
                    Media::delete($old);
                    return Media::storeVideo($file);
                }
                if (!empty($row[$key . '__remove'])) {
                    Media::delete($old);
                    return '';
                }
                $link = trim((string) $value);
                if ($link === '') {
                    return Media::isUpload($old) ? $old : '';   // an uploaded file stays until it is replaced or removed
                }
                if (!Html::safeUrl($link)) {
                    return $old;
                }
                if ($link !== $old) {
                    Media::delete($old);                       // a link replaces the uploaded file
                }
                return mb_substr($link, 0, 500);

            case 'url':
                $url = trim((string) $value);
                return ($url === '' || Html::safeUrl($url)) ? mb_substr($url, 0, 500) : (is_string($current) ? $current : '');

            case 'lines':
                $lines = is_string($value) ? preg_split('/\R/', $value) : (array) $value;
                return array_values(array_filter(array_map(fn ($l) => $this->text((string) $l, $current), $lines), fn ($l) => $l !== ''));

            case 'repeater':
                return $this->castRepeater($def, (array) $value, $current, is_array($file) ? $file : []);

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

    private function castRepeater(array $def, array $rows, mixed $current = null, array $files = []): array
    {
        $out = [];
        $positional = array_filter(array_keys($def['fields']), 'is_int') === array_keys($def['fields']);
        $stored = array_filter(Arr::flatten((array) $current), 'is_string');   // a posted "keep" value must be one of these
        foreach ($rows as $m => $row) {
            if (!is_array($row) || (string) $m === '__M__') {
                continue;
            }
            $item = [];
            $empty = true;
            foreach ($def['fields'] as $k => $sub) {
                if ($sub['type'] === 'video') {
                    $keep = (string) ($row[$k . '__keep'] ?? '');
                    $v = $this->cast($sub, $row[$k] ?? '', $row, $files[$m][$k . '__file'] ?? null, in_array($keep, $stored, true) ? $keep : '', (string) $k);
                } else {
                    $v = $this->cast($sub, $row[$k] ?? '', $row, null, $current, (string) $k);
                }
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
        // uploaded files of rows that were removed go away with them
        $kept = Arr::flatten($out);
        foreach ($stored as $path) {
            if (Media::isUpload($path) && !in_array($path, $kept, true)) {
                Media::delete($path);
            }
        }
        return $out;
    }

    private function assertImage(UploadedFile $file, string $label): void
    {
        $v = Validator::make(['f' => $file], ['f' => Media::rule(true)], [], ['f' => $label]);
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
