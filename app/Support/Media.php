<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Uploaded images live in public/uploads (the "uploads" disk), so no storage:link symlink is needed on
 * Windows/XAMPP. Stored values are paths relative to /public, exactly like the original "assets/..." paths,
 * which means templates print both kinds of image the same way.
 */
class Media
{
    public const DISK = 'uploads';
    public const IMAGE_MIMES = 'jpg,jpeg,png,webp,gif';
    public const MAX_KB = 5120;

    /** Validation rule for an image field. SVG is opt-in (logos/icons) and is sanitised on upload. */
    public static function rule(bool $allowSvg = false): array
    {
        $mimes = self::IMAGE_MIMES . ($allowSvg ? ',svg' : '');
        return ['nullable', 'file', 'mimes:' . $mimes, 'max:' . self::MAX_KB];
    }

    public static function store(UploadedFile $file, string $folder = 'images'): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
        $relative = trim($folder, '/') . '/' . now()->format('Y/m') . '/' . Str::limit($name, 40, '') . '-' . Str::lower(Str::random(8)) . '.' . $ext;

        if ($ext === 'svg') {
            $svg = self::sanitizeSvg((string) file_get_contents($file->getRealPath()));
            Storage::disk(self::DISK)->put($relative, $svg);
        } else {
            $stored = Storage::disk(self::DISK)->putFileAs(dirname($relative), $file, basename($relative));
            if ($stored === false) {
                throw new RuntimeException('The file could not be saved.');
            }
        }

        return 'uploads/' . $relative;
    }

    public static function isUpload(?string $path): bool
    {
        return is_string($path) && str_starts_with($path, 'uploads/');
    }

    /** Remove a previously uploaded file. Original site assets ("assets/...") are never deleted. */
    public static function delete(?string $path): void
    {
        if (self::isUpload($path)) {
            Storage::disk(self::DISK)->delete(substr($path, strlen('uploads/')));
        }
    }

    /** Absolute URL for admin previews. */
    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        return preg_match('#^https?://#i', $path) ? $path : asset(ltrim($path, '/'));
    }

    /**
     * URL to preview an editor value. Values are either full paths ("uploads/…", "assets/…") or the bare file names
     * the original site used ("thumb-1.png", "collaterals/services.png"), which are looked up under assets/.
     */
    public static function preview(?string $value): ?string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return null;
        }
        if (preg_match('#^(https?:)?//#', $v)) {
            return $v;
        }
        if (str_starts_with($v, 'uploads/') || str_starts_with($v, 'assets/')) {
            return asset($v);
        }
        foreach (['assets/images/', 'assets/icons/'] as $dir) {
            if (is_file(public_path($dir . $v))) {
                return asset($dir . $v);
            }
        }
        $found = self::assetIndex()[basename($v)] ?? null;
        return $found ? asset($found) : null;
    }

    /** basename => relative path for every original image/icon (cached; built once). */
    private static function assetIndex(): array
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('media.asset_index', function () {
            $map = [];
            foreach (['assets/images', 'assets/icons'] as $root) {
                $base = public_path($root);
                if (!is_dir($base)) {
                    continue;
                }
                $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
                foreach ($it as $file) {
                    if ($file->isFile() && preg_match('/\.(png|jpe?g|webp|gif|svg)$/i', $file->getFilename())) {
                        $rel = $root . '/' . str_replace('\\', '/', substr($file->getPathname(), strlen($base) + 1));
                        $map[$file->getFilename()] ??= $rel;
                    }
                }
            }
            return $map;
        });
    }

    public static function sanitizeSvg(string $svg): string
    {
        if (stripos($svg, '<!ENTITY') !== false || stripos($svg, '<!DOCTYPE') !== false) {
            throw new RuntimeException('That SVG file contains unsupported declarations.');
        }
        $doc = new DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $ok = $doc->loadXML($svg, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        if (!$ok || !$doc->documentElement || strtolower($doc->documentElement->localName) !== 'svg') {
            throw new RuntimeException('That SVG file could not be read.');
        }
        $remove = [];
        foreach ($doc->getElementsByTagName('*') as $el) {
            /** @var DOMElement $el */
            if (in_array(strtolower($el->localName), ['script', 'foreignobject', 'iframe', 'embed', 'object'], true)) {
                $remove[] = $el;
                continue;
            }
            foreach (iterator_to_array($el->attributes) as $attr) {
                $name = strtolower($attr->localName);
                $val = strtolower(trim($attr->value));
                if (str_starts_with($name, 'on') || (in_array($name, ['href'], true) && !str_starts_with($val, '#') && !str_starts_with($val, 'data:image/'))) {
                    $el->removeAttributeNode($attr);
                }
            }
        }
        foreach ($remove as $el) {
            $el->parentNode?->removeChild($el);
        }
        return $doc->saveXML($doc->documentElement);
    }
}
