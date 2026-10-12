<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Page;
use App\Support\Html;
use App\Support\Media;
use App\Support\Search;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    /** Addresses that must never be taken by a custom page. */
    private const RESERVED = ['admin', 'up', 'assets', 'uploads', 'storage', 'index', 'login', 'logout', 'build', 'vendor', 'api', 'inquiries'];

    private const SORTS = ['title', 'status', 'updated_at', 'sort'];

    public function index(Request $request): View
    {
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'sort';
        $dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        $pages = Page::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = Search::like($request->string('q')->toString());
                $q->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('slug', 'like', $like)->orWhere('meta_title', 'like', $like));
            })
            ->when(in_array($request->query('status'), array_keys(Page::STATUSES), true), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->query('type') === 'system', fn ($q) => $q->where('is_system', true))
            ->when($request->query('type') === 'custom', fn ($q) => $q->where('is_system', false))
            ->orderBy($sort, $dir)->orderBy('id')
            ->paginate(15)->withQueryString();

        return view('admin.pages.index', ['pages' => $pages, 'sort' => $sort, 'dir' => $dir]);
    }

    public function create(): View
    {
        $page = new Page(['status' => Page::STATUS_DRAFT]);
        return view('admin.pages.form', ['page' => $page]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $page = new Page();
        $page->slug = $data['slug'];
        $page->template = 'generic';
        $page->is_system = false;
        $this->fillPage($request, $page, $data);
        $page->sort = (int) Page::max('sort') + 1;
        $page->save();

        ActivityLog::record('created', "Created page “{$page->title}”", $page);
        return redirect()->route('admin.pages.edit', $page)->with('success', 'The page was created' . ($page->isPublished() ? ' and is live.' : ' as a draft.'));
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate($this->rules($page));

        if ($page->slug === 'index' && $data['status'] !== Page::STATUS_PUBLISHED) {
            return back()->withInput()->withErrors(['status' => 'The home page has to stay published.']);
        }
        if (!$page->is_system && isset($data['slug'])) {
            $page->slug = $data['slug'];
        }
        $this->fillPage($request, $page, $data);
        $page->save();

        ActivityLog::record('updated', "Updated page “{$page->title}”", $page);
        return redirect()->route('admin.pages.edit', $page)->with('success', 'Page saved.');
    }

    public function toggleStatus(Request $request, Page $page): RedirectResponse
    {
        if ($page->slug === 'index' && $page->isPublished()) {
            return back()->with('error', 'The home page has to stay published.');
        }
        $page->status = $page->isPublished() ? Page::STATUS_DRAFT : Page::STATUS_PUBLISHED;
        $page->published_at = $page->isPublished() ? ($page->published_at ?? now()) : $page->published_at;
        $page->updated_by = $request->user()->id;
        $page->save();

        ActivityLog::record('updated', ($page->isPublished() ? 'Published' : 'Unpublished') . " page “{$page->title}”", $page);
        return back()->with('success', $page->isPublished() ? "“{$page->title}” is now live." : "“{$page->title}” was unpublished and is hidden from visitors.");
    }

    public function destroy(Page $page): RedirectResponse
    {
        if ($page->is_system) {
            return back()->with('error', 'The original website pages cannot be deleted. Unpublish them instead.');
        }
        Media::delete($page->featured_image);
        Media::delete($page->og_image);
        $title = $page->title;
        $page->forceDelete();               // really gone, so the same web address can be used again (slug is unique)

        ActivityLog::record('deleted', "Deleted page “{$title}”");
        return redirect()->route('admin.pages.index')->with('success', "Page “{$title}” was deleted.");
    }

    /* ------------------------------------------------------------------ */

    private function rules(?Page $page = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:160'],
            'status' => ['required', Rule::in(array_keys(Page::STATUSES))],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'meta_keywords' => ['nullable', 'string', 'max:300'],
            'og_image' => Media::rule(false),
            'featured_image' => Media::rule(false),
            'content' => ['nullable', 'string', 'max:200000'],
        ];

        if (!$page || !$page->is_system) {
            $rules['slug'] = [
                'required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn(self::RESERVED),
                Rule::unique('pages', 'slug')->ignore($page?->id)->withoutTrashed(),
            ];
        }
        return $rules;
    }

    private function fillPage(Request $request, Page $page, array $data): void
    {
        $page->fill([
            'title' => Html::plain($data['title']),
            'meta_title' => isset($data['meta_title']) ? Html::plain($data['meta_title']) : null,
            'meta_description' => isset($data['meta_description']) ? Html::plain($data['meta_description']) : null,
            'meta_keywords' => isset($data['meta_keywords']) ? Html::plain($data['meta_keywords']) : null,
            'status' => $data['status'],
        ]);
        if (!$page->is_system) {
            $page->content = Html::rich($data['content'] ?? '');
        }
        if ($page->isPublished() && !$page->published_at) {
            $page->published_at = now();
        }
        $page->updated_by = $request->user()->id;

        foreach (['featured_image', 'og_image'] as $col) {
            if ($file = $request->file($col)) {
                Media::delete($page->{$col});
                $page->{$col} = Media::store($file, 'pages');
            } elseif ($request->boolean("remove_{$col}")) {
                Media::delete($page->{$col});
                $page->{$col} = null;
            }
        }
    }
}
