<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use App\Models\Page;
use App\Support\Html;
use App\Support\SiteMenus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * "Services & menus": the header mega-menu (location = header) and the footer link columns (location = footer).
 * Footer columns are fixed by the design (their headings can be renamed, links edited); header groups can be added/removed.
 */
class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'footer' ? 'footer' : 'header';

        return view('admin.menu.index', [
            'tab' => $tab,
            'groups' => MenuGroup::at($tab)->with(['items.page'])->get(),
            'pages' => Page::orderBy('title')->get(['id', 'title', 'slug', 'is_system', 'status']),
            'counts' => ['header' => MenuGroup::at('header')->count(), 'footer' => MenuGroup::at('footer')->count()],
        ]);
    }

    /* ---------------- groups */

    public function storeGroup(Request $request): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:80']]);
        $group = new MenuGroup();
        $group->forceFill(['location' => 'header', 'title' => Html::plain($data['title']), 'sort' => (int) MenuGroup::where('location', 'header')->max('sort') + 1, 'is_published' => true])->save();
        $this->done('created', "Added service group “{$group->title}”", $group);

        return back()->with('success', "Group “{$group->title}” was added. Now add its links.");
    }

    public function updateGroup(Request $request, MenuGroup $group): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:80'], 'is_published' => ['nullable', 'boolean']]);
        $group->title = Html::plain($data['title']);
        if ($group->location === 'header') {
            $group->is_published = $request->boolean('is_published');
        }
        $group->save();
        $this->done('updated', "Updated menu group “{$group->title}”", $group);

        return $this->back($group)->with('success', 'Group saved.');
    }

    public function moveGroup(Request $request, MenuGroup $group): RedirectResponse
    {
        $this->swap(MenuGroup::at($group->location)->get(), $group, $request->input('dir'));
        $this->flush();
        return $this->back($group);
    }

    public function destroyGroup(MenuGroup $group): RedirectResponse
    {
        if ($group->location !== 'header') {
            return back()->with('error', 'Footer columns are part of the footer design and cannot be deleted. You can edit their links instead.');
        }
        $title = $group->title;
        $group->delete();                   // its links go with it (cascade)
        $this->done('deleted', "Deleted service group “{$title}”");

        return redirect()->route('admin.menu.index', ['tab' => 'header'])->with('success', "Group “{$title}” and its links were deleted.");
    }

    /* ---------------- links */

    public function storeItem(Request $request, MenuGroup $group): RedirectResponse
    {
        $data = $request->validate($this->itemRules());
        $item = new MenuItem();
        $item->menu_group_id = $group->id;
        $this->fillItem($item, $request, $data);
        $item->sort = (int) $group->items()->max('sort') + 1;
        $item->save();
        $this->done('created', "Added link “{$item->label}” to “{$group->title}”", $item);

        return $this->back($group)->with('success', "“{$item->label}” was added.");
    }

    public function updateItem(Request $request, MenuItem $item): RedirectResponse
    {
        $data = $request->validate($this->itemRules());
        $this->fillItem($item, $request, $data);
        $item->save();
        $this->done('updated', "Updated link “{$item->label}”", $item);

        return $this->back($item->group)->with('success', 'Link saved.');
    }

    public function moveItem(Request $request, MenuItem $item): RedirectResponse
    {
        $this->swap($item->group->items()->get(), $item, $request->input('dir'));
        $this->flush();
        return $this->back($item->group);
    }

    public function destroyItem(MenuItem $item): RedirectResponse
    {
        $group = $item->group;
        $label = $item->label;
        $item->delete();
        $this->done('deleted', "Deleted link “{$label}”");

        return $this->back($group)->with('success', "“{$label}” was removed.");
    }

    /* ---------------- helpers */

    private function itemRules(): array
    {
        return [
            'label' => ['required', 'string', 'max:120'],
            'page_id' => ['nullable', Rule::exists('pages', 'id')->whereNull('deleted_at')],
            'url' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
            'new_tab' => ['nullable', 'boolean'],
        ];
    }

    private function fillItem(MenuItem $item, Request $request, array $data): void
    {
        $url = trim((string) ($data['url'] ?? ''));
        if ($url !== '' && !Html::safeUrl($url)) {
            abort(422, 'That link is not allowed.');
        }
        $item->label = Html::plain($data['label']);
        $item->page_id = $data['page_id'] ?? null;
        $item->url = $url !== '' ? $url : '#';
        $item->is_published = $request->boolean('is_published');
        $item->new_tab = $request->boolean('new_tab');
    }

    /** Swap the sort position of a record with its neighbour, then renumber so positions stay unique. */
    private function swap($siblings, $record, ?string $dir): void
    {
        $list = $siblings->values();
        $i = $list->search(fn ($r) => $r->is($record));
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if ($i === false || $j < 0 || $j >= $list->count()) {
            return;
        }
        [$list[$i], $list[$j]] = [$list[$j], $list[$i]];
        foreach ($list as $n => $r) {
            if ((int) $r->sort !== $n + 1) {
                $r->forceFill(['sort' => $n + 1])->save();
            }
        }
    }

    private function back(MenuGroup $group): RedirectResponse
    {
        return redirect()->route('admin.menu.index', ['tab' => $group->location])->withFragment('group-' . $group->id);
    }

    private function done(string $action, string $text, $subject = null): void
    {
        ActivityLog::record($action, $text, $subject);
        $this->flush();
    }

    private function flush(): void
    {
        SiteMenus::flush();
    }
}
