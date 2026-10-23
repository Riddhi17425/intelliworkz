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
use App\Support\SectionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            'sections' => $this->pageSections($page),
            'intro' => 'Change the text, pictures and lists on this page. Colours, spacing and layout are fixed, so the design cannot be broken here.',
            'seoUrl' => route('admin.pages.edit', $page),
            'page' => $page,
            'layout' => $page->hasSections() ? $this->layoutRows($page) : null,
            'designs' => $page->hasSections() ? SectionPlan::designs() : [],
            'sectionLabels' => SectionPlan::LABELS,
        ]);
    }

    /** Add a section of another design (a private copy of its content) to the page. */
    public function addSection(Request $request, Page $page): RedirectResponse
    {
        abort_unless($page->hasSections(), 404);
        $data = $request->validate([
            'tpl' => ['required', Rule::in(array_keys(SectionPlan::designs()))],
            'key' => ['required', Rule::in(SectionPlan::KEYS)],
        ]);
        $item = SectionPlan::add($page, $data['tpl'], $data['key']);
        $design = SectionPlan::designs()[$data['tpl']] ?? $data['tpl'];
        ActivityLog::record('updated', "Added section “" . SectionPlan::label($item['key']) . "” from “{$design}” to “{$page->title}”", $page);

        return redirect()->to(route('admin.pages.content', $page) . '#sec-' . Str::slug($item['id'] . '-' . SectionPlan::label($item['key'])))
            ->with('success', 'The section was added at the end of the page. Move it where you want it, edit its text, then save.');
    }

    public function removeSection(Page $page, string $id): RedirectResponse
    {
        abort_unless($page->hasSections(), 404);
        abort_unless(SectionPlan::remove($page, $id), 404);
        ActivityLog::record('updated', "Removed an added section from “{$page->title}”", $page);

        return redirect()->to(route('admin.pages.content', $page) . '#layoutCard')->with('success', 'The section was removed from the page.');
    }

    public function updatePage(Request $request, Page $page): RedirectResponse
    {
        abort_unless($page->hasEditor(), 404);
        $changed = $this->save($request, $page->slug);
        if ($page->hasSections()) {
            foreach (SectionPlan::extraScopes($page) as $scope) {
                $changed += $this->save($request, $scope);
            }
            if ($request->boolean('has_layout_form')) {
                $changed += $this->saveLayout($request, $page);
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
    /** Own sections of a page, followed by the sections copied in from other designs. */
    private function pageSections(Page $page): array
    {
        $out = $this->sections($page->slug, '', null, $page->viewKey());
        if (!$page->hasSections()) {
            return $out;
        }
        $designs = SectionPlan::designs();
        foreach (SectionPlan::layout($page) as $item) {
            if ($item['tpl']) {
                $badge = SectionPlan::label($item['key']) . ' · added from ' . ($designs[$item['tpl']] ?? $item['tpl']);
                $out = array_merge($out, $this->sections(SectionPlan::scope($page, $item), $item['id'] . '-', $badge, $item['tpl']));
            }
        }
        return $out;
    }

    /** Rows of the "Sections on this page" panel. */
    private function layoutRows(Page $page): array
    {
        $designs = SectionPlan::designs();
        return array_map(fn (array $i) => $i + [
            'label' => SectionPlan::label($i['key']),
            'design' => $i['tpl'] ? ($designs[$i['tpl']] ?? $i['tpl']) : null,
            'removeUrl' => $i['tpl'] ? route('admin.pages.sections.remove', [$page, $i['id']]) : null,
        ], SectionPlan::layout($page));
    }

    private function saveLayout(Request $request, Page $page): int
    {
        $current = [];
        foreach (SectionPlan::layout($page) as $item) {
            $current[$item['id']] = $item;
        }
        $new = [];
        foreach ((array) $request->input('layout', []) as $row) {
            $id = is_array($row) ? ($row['id'] ?? null) : null;
            if (!$id || !isset($current[$id]) || isset($new[$id])) {
                continue;
            }
            $new[$id] = ['id' => $id, 'key' => $current[$id]['key'], 'tpl' => $current[$id]['tpl'], 'hidden' => empty($row['show'])];
        }
        foreach ($current as $id => $item) {          // anything the form did not mention stays, at the end
            $new[$id] ??= $item;
        }
        $new = array_values($new);

        if ($new === SectionPlan::layout($page)) {
            return 0;
        }
        $page->layout = $new === SectionPlan::defaultLayout() ? null : $new;
        $page->save();
        return 1;
    }

    /**
     * The sections of the editor for one scope. Texts are grouped by their section; each list joins the section of the
     * website it is part of (config/content_sections.php), so one visual section is edited in one place.
     *
     * @param ?string $design the design (template) the scope belongs to, for design specific placement
     */
    private function sections(string $scope, string $idPrefix = '', ?string $badge = null, ?string $design = null): array
    {
        $fields = ContentField::where('scope', $scope)->orderBy('sort')->get();
        $lists = ContentList::where('scope', $scope)->orderBy('sort')->orderBy('id')->get();
        $merge = (array) config('content_sections.merge', []);
        $join = array_merge((array) config('content_sections.join', []), (array) config("content_sections.per.{$design}", []));
        $place = (array) config("content_sections.place.{$design}", []);

        $out = [];
        $sectionFor = function (string $name) use (&$out, $idPrefix, $badge) {
            $id = Str::slug($idPrefix . $name) ?: 'section';
            return $out[$id] ??= ['name' => $name . ($badge ? " — {$badge}" : ''), 'id' => $id, 'fields' => collect(), 'lists' => []];
        };

        $fieldSections = [];
        foreach ($fields->groupBy('section') as $name => $group) {
            $name = $merge[$name] ?? (string) $name;
            $section = $sectionFor($name);
            $out[$section['id']]['fields'] = $out[$section['id']]['fields']->concat($group)->values();
            $fieldSections[$name] = true;
        }

        $after = [];                                  // section id => id of the section it must follow
        foreach ($lists as $list) {
            $schema = ListSchema::make($list->key, $list->items ?? []);
            if ($schema->hidden()) {
                continue;
            }
            $label = (string) ($list->section ?: 'Lists');
            $label = $merge[$label] ?? $label;
            $target = $label;
            foreach ($join[$label] ?? [] as $candidate) {
                if (isset($fieldSections[$candidate])) {
                    $target = $candidate;
                    break;
                }
            }
            if ($target === $label && !isset($fieldSections[$label]) && isset($place[$label])) {
                $target = $place[$label]['name'];
                $after[Str::slug($idPrefix . $target)] = Str::slug($idPrefix . $place[$label]['after']);
            }
            $section = $sectionFor($target);
            $out[$section['id']]['lists'][] = ['model' => $list, 'schema' => $schema];
        }

        $out = array_values(array_filter($out, fn ($s) => $s['fields']->isNotEmpty() || $s['lists']));

        foreach ($after as $id => $follows) {         // a section made only of lists goes right after the section it follows on the website
            $from = array_search($id, array_column($out, 'id'), true);
            $to = array_search($follows, array_column($out, 'id'), true);
            if ($from === false || $to === false) {
                continue;
            }
            $moved = array_splice($out, $from, 1);
            $to = array_search($follows, array_column($out, 'id'), true);
            array_splice($out, $to + 1, 0, $moved);
        }

        return $out;
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

        if ($field->type === 'toggle') {
            return $raw === '0' ? '0' : '1';
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
