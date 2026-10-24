<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SiteSetting;
use App\Support\Html;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class SettingController extends Controller
{
    /** Settings that may carry an SVG (logos/badges); the favicon and share image stay raster. */
    private const NO_SVG = ['favicon'];   // browsers do not all accept an SVG favicon

    public function edit(): View
    {
        $settings = SiteSetting::query()->orderBy('sort')->get()->groupBy('group');
        return view('admin.settings.edit', ['groups' => SiteSetting::GROUPS, 'settings' => $settings]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rows = SiteSetting::query()->get()->keyBy('key');

        // The rules are built from the stored definitions, so only known settings can ever be written.
        $rules = [];
        foreach ($rows as $key => $row) {
            $rules["s.{$key}"] = match ($row->type) {
                'image' => Media::rule(!in_array($key, self::NO_SVG, true)),
                'email' => ['nullable', 'email:rfc', 'max:190'],
                'url' => ['nullable', 'string', 'max:300', 'regex:/^(#|https?:\/\/.+)$/i'],
                'textarea' => ['nullable', 'string', 'max:2000'],
                default => ['nullable', 'string', 'max:300'],
            };
        }
        $data = $request->validate($rules, [
            's.*.regex' => 'Enter a full web address starting with http:// or https://.',
        ]);

        $changed = [];
        foreach ($rows as $key => $row) {
            if ($row->type === 'image') {
                if ($file = $request->file("s.{$key}")) {
                    try {
                        $path = Media::store($file, 'branding');
                    } catch (RuntimeException $e) {
                        return back()->withInput()->withErrors(["s.{$key}" => $e->getMessage()]);
                    }
                    Media::delete($row->value);
                    $row->value = $path;
                } elseif ($request->boolean("remove_{$key}")) {
                    Media::delete($row->value);
                    $row->value = null;
                } else {
                    continue;
                }
            } else {
                $new = trim((string) ($data['s'][$key] ?? ''));
                $new = $row->type === 'textarea'
                    ? trim(preg_replace("/[ \t]+/", ' ', strip_tags(str_replace("\r\n", "\n", $new))))   // keep line breaks (footer uses nl2br)
                    : Html::plain($new);
                if ($new === (string) $row->value) {
                    continue;
                }
                $row->value = $new === '' ? null : $new;
            }
            $row->save();
            $changed[] = $row->label;
        }

        if ($changed) {
            ActivityLog::record('updated', 'Changed site settings: ' . implode(', ', $changed));
        }
        return redirect()->route('admin.settings.edit', ['tab' => $request->input('tab')])
            ->with('success', $changed ? 'Settings saved — the website is updated.' : 'Nothing was changed.');
    }
}
