<?php

namespace App\Support;

use App\Models\ContentField;
use App\Models\ContentList;
use App\Models\Page;
use Illuminate\Support\Facades\DB;

/**
 * Makes a new page that looks exactly like an existing one: same design (template), same sections,
 * and a private copy of all texts, lists and pictures that the editor can change without touching the original.
 */
class PageCloner
{
    public function clone(Page $source, string $slug, string $title): Page
    {
        return DB::transaction(function () use ($source, $slug, $title) {
            $page = new Page(['title' => $title, 'status' => Page::STATUS_DRAFT]);
            $page->slug = $slug;
            $page->template = $source->viewKey();
            $page->is_system = false;
            $page->layout = $source->layout;
            $page->sort = (int) Page::max('sort') + 1;
            $page->save();

            foreach (ContentField::where('scope', $source->slug)->orderBy('id')->get() as $f) {
                $copy = $f->replicate();
                $copy->scope = $slug;
                if ($f->type === 'image') {
                    $copy->value = Media::duplicate($f->value);
                }
                $copy->save();
            }
            foreach (ContentList::where('scope', $source->slug)->orderBy('id')->get() as $l) {
                $copy = $l->replicate();
                $copy->scope = $slug;
                $copy->items = $this->duplicateUploads($l->items ?? []);
                $copy->save();
            }
            Content::forget($slug);

            // sections copied in from other designs keep a private copy of their content under the new address
            foreach (SectionPlan::layout($source) as $item) {
                if ($item['tpl']) {
                    $this->copyScope(SectionPlan::scope($source, $item), $slug . '~' . $item['id']);
                }
            }

            return $page;
        });
    }

    private function copyScope(string $from, string $to): void
    {
        foreach (ContentField::where('scope', $from)->orderBy('id')->get() as $f) {
            $copy = $f->replicate();
            $copy->scope = $to;
            if ($f->type === 'image') {
                $copy->value = Media::duplicate($f->value);
            }
            $copy->save();
        }
        foreach (ContentList::where('scope', $from)->orderBy('id')->get() as $l) {
            $copy = $l->replicate();
            $copy->scope = $to;
            $copy->items = $this->duplicateUploads($l->items ?? []);
            $copy->save();
        }
        Content::forget($to);
    }

    /** Removes the content (and uploaded pictures) of a page that is being deleted, including its copied-in sections. */
    public function purgePage(Page $page): void
    {
        foreach (SectionPlan::extraScopes($page) as $scope) {
            $this->purge($scope);
        }
        $this->purge($page->slug);
    }

    /** Removes the content (and uploaded pictures) of one scope. */
    public function purge(string $scope): void
    {
        foreach (ContentField::where('scope', $scope)->where('type', 'image')->get() as $f) {
            Media::delete($f->value);
        }
        foreach (ContentList::where('scope', $scope)->get() as $l) {
            $this->walk($l->items ?? [], fn (string $v) => Media::delete($v));
        }
        ContentField::where('scope', $scope)->delete();
        ContentList::where('scope', $scope)->delete();
        Content::forget($scope);
    }

    /** The address of a page changed: its content (and that of its copied-in sections) follows it. */
    public function renamePage(Page $page, string $to): void
    {
        foreach (SectionPlan::extraScopes($page) as $id => $scope) {
            $this->rename($scope, $to . '~' . $id);
        }
        $this->rename($page->slug, $to);
    }

    public function rename(string $from, string $to): void
    {
        ContentField::where('scope', $from)->update(['scope' => $to]);
        ContentList::where('scope', $from)->update(['scope' => $to]);
        Content::forget($from);
        Content::forget($to);
    }

    private function duplicateUploads(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($v) => $this->duplicateUploads($v), $value);
        }
        return is_string($value) && Media::isUpload($value) ? Media::duplicate($value) : $value;
    }

    private function walk(mixed $value, callable $fn): void
    {
        if (is_array($value)) {
            foreach ($value as $v) {
                $this->walk($v, $fn);
            }
        } elseif (is_string($value) && Media::isUpload($value)) {
            $fn($value);
        }
    }
}
