<?php

namespace App\Http\Controllers\Admin;

use App\Content\ListSchema;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContentField;
use App\Models\ContentList;
use App\Models\Page;
use App\Support\Content;
use App\Support\Html;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Edits the copy, images and lists of one page (scope = its slug) or of the shared header/footer (scope "global"). */
class ContentController extends Controller
{
    public function editPage(Page $page): View
    {
        abort_unless($page->hasEditor(), 404);

        return view('admin.content.edit', [
            'title' => $page->title . ' — content',
            'crumbs' => ['Pages' => route('admin.pages.index'), $page->title => route('admin.pages.edit', $page), 'Content' => null],
            'action' => route('admin.pages.content.update', $page),
            'viewUrl' => $page->url(),
            'sections' => $this->sections($page->slug),
            'intro' => 'Change the text, pictures and lists on this page. Colours, spacing and layout are fixed, so the design cannot be broken here.',
            'seoUrl' => route('admin.pages.edit', $page),
            'toggles' => $page->sectionToggles(),
            'page' => $page,
        ]);
    }

    public function updatePage(Request $request, Page $page): RedirectResponse
    {
        abort_unless($page->hasEditor(), 404);
        $changed = $this->save($request, $page->slug);

        // section switches: anything not ticked is hidden (only when the form carried the switches)
        $toggles = array_keys($page->sectionToggles());
        if ($toggles && $request->boolean('has_section_switches')) {
            $visible = array_intersect($toggles, array_keys((array) $request->input('show_sections', [])));
            $hidden = array_values(array_diff($toggles, $visible));
            if (($page->hidden_sections ?? []) !== $hidden) {
                $page->hidden_sections = $hidden ?: null;
                $page->save();
                $changed++;
            }
        }
        $page->forceFill(['updated_by' => $request->user()->id])->touch();
        ActivityLog::record('updated', "Edited content of “{$page->title}” ({$changed} changes)", $page);

        return back()->with('success', $changed ? 'Content saved. The website is already showing the changes.' : 'Nothing was changed.');
    }

    public function editShared(): View
    {
        return view('admin.content.edit', [
            'title' => 'Header, footer & shared sections',
            'crumbs' => ['Header, footer & shared' => null],
            'action' => route('admin.shared.update'),
            'viewUrl' => url('/'),
            'sections' => $this->sections('global'),
            'intro' => 'This text appears on every page: the header buttons, the footer call-to-action, offices, the dark showcase slider and the engagement models.',
            'seoUrl' => null,
        ]);
    }

    public function updateShared(Request $request): RedirectResponse
    {
        $changed = $this->save($request, 'global');
        ActivityLog::record('updated', "Edited shared header/footer content ({$changed} changes)");

        return back()->with('success', $changed ? 'Shared content saved for every page.' : 'Nothing was changed.');
    }

    /* ------------------------------------------------------------------ building the form */

    /** @return list<array{name:string,id:string,fields:\Illuminate\Support\Collection,lists:list<array{model:ContentList,schema:ListSchema}>}> */
    private function sections(string $scope): array
    {
        $fields = ContentField::where('scope', $scope)->orderBy('sort')->get();
        $lists = ContentList::where('scope', $scope)->orderBy('sort')->orderBy('id')->get();

        $out = [];
        $sectionFor = function (string $name) use (&$out) {
            $id = Str::slug($name) ?: 'section';
            return $out[$id] ??= ['name' => $name, 'id' => $id, 'fields' => collect(), 'lists' => []];
        };

        foreach ($fields->groupBy('section') as $name => $group) {
            $section = $sectionFor((string) $name);
            $out[$section['id']]['fields'] = $group->values();
        }
        foreach ($lists as $list) {
            $schema = ListSchema::make($list->key, $list->items ?? []);
            if ($schema->hidden()) {
                continue;
            }
            $section = $sectionFor((string) ($list->section ?: 'Lists'));
            $out[$section['id']]['lists'][] = ['model' => $list, 'schema' => $schema];
        }

        return array_values(array_filter($out, fn ($s) => $s['fields']->isNotEmpty() || $s['lists']));
    }

    /* ------------------------------------------------------------------ saving */

    private function save(Request $request, string $scope): int
    {
        $request->validate([
            'fields' => ['nullable', 'array'],
            'fields.*' => ['nullable', 'string', 'max:20000'],
            'images' => ['nullable', 'array'],
            'lists' => ['nullable', 'array'],
        ]);

        $changed = 0;

        DB::transaction(function () use ($request, $scope, &$changed) {
            foreach (ContentField::where('scope', $scope)->get() as $field) {
                $new = $this->fieldValue($request, $field);
                if ($new !== null && $new !== (string) $field->value) {
                    $field->value = $new;
                    $field->save();
                    $changed++;
                }
            }

            foreach (ContentList::where('scope', $scope)->get() as $list) {
                if (!$request->has("lists.{$list->id}")) {
                    continue;
                }
                $schema = ListSchema::make($list->key, $list->items ?? []);
                $post = (array) $request->input("lists.{$list->id}");
                $files = (array) ($request->allFiles()['lists'][$list->id] ?? []);
                $items = $schema->apply($post, $files);
                if ($items !== ($list->items ?? [])) {
                    $list->items = $items;
                    $list->save();
                    $changed++;
                }
            }
        });

        Content::forget($scope);

        return $changed;
    }

    /** New stored value for one field, or null when it was not on the form. */
    private function fieldValue(Request $request, ContentField $field): ?string
    {
        $id = $field->id;

        if ($field->type === 'image') {
            $file = $request->file("images.{$id}");
            if ($file instanceof UploadedFile && $file->isValid()) {
                $v = Validator::make(['f' => $file], ['f' => Media::rule(false)], [], ['f' => $field->label]);
                if ($v->fails()) {
                    throw ValidationException::withMessages(["images.{$id}" => $v->errors()->first('f')]);
                }
                Media::delete($field->value);
                return Media::store($file, 'images');
            }
            if ($request->boolean("remove_images.{$id}")) {
                Media::delete($field->value);
                return '';
            }
            return null;
        }

        if (!$request->has("fields.{$id}")) {
            return null;
        }
        $raw = (string) $request->input("fields.{$id}");

        // untouched: what the browser sends back is exactly what the box showed, so leave the stored value alone
        $norm = fn (string $s) => trim(str_replace("\r\n", "\n", $s));
        if ($norm($raw) === $norm(Html::forInput($field->value))) {
            return null;
        }

        if ($field->type === 'url') {
            $url = trim($raw);
            if ($url !== '' && !Html::safeUrl($url)) {
                throw ValidationException::withMessages(["fields.{$id}" => 'That link is not allowed. Use https://…, mailto:, tel:, a page like contact-us.php, or #.']);
            }
            return $url;
        }

        return Html::clean($raw);
    }
}
