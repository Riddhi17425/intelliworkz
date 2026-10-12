@extends('admin.layouts.app')
@php $editing = $page->exists; @endphp
@section('title', $editing ? $page->title : 'New page')

@section('content')
<x-admin.head :title="$editing ? $page->title : 'New page'" :crumbs="['Pages' => route('admin.pages.index'), ($editing ? 'Settings & SEO' : 'New page') => null]">
    <x-slot:actions>
        @if($editing && $page->is_system)<a href="{{ route('admin.pages.content', $page) }}" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i> Edit page content</a>@endif
        @if($editing)<a href="{{ $page->url() }}" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right me-1"></i> View</a>@endif
    </x-slot:actions>
</x-admin.head>

<form method="post" action="{{ $editing ? route('admin.pages.update', $page) : route('admin.pages.store') }}" enctype="multipart/form-data" data-dirty-guard data-once>
    @csrf @if($editing) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="adm-card mb-3">
                <div class="adm-card__head"><h2>{{ $page->is_system ? 'Page details' : 'Content' }}</h2></div>
                <div class="adm-card__body">
                    <x-admin.field name="title" label="Page title" :value="$page->title" :required="true" placeholder="e.g. Careers" :attrs="['maxlength' => 160]" />
                    @unless($page->is_system)
                        <div class="mb-3">
                            <label class="form-label" for="f_slug">Web address<span class="req">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ rtrim(url('/'), '/') }}/</span>
                                <input id="f_slug" type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $page->slug) }}" data-slug-from="#f_title" placeholder="careers" maxlength="100" required>
                            </div>
                            @error('slug')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            <div class="form-text">Lower-case letters, numbers and hyphens only. @if($editing)<strong>Changing it breaks existing links to this page.</strong>@endif</div>
                        </div>
                        <x-admin.field name="content" type="rich" label="Page content" :value="$page->content" />
                        <x-admin.image name="featured_image" label="Featured image" :current="$page->featured_image" help="Shown at the top of the page. PNG, JPG, WebP or GIF up to 5 MB." />
                    @else
                        <div class="alert alert-light border small mb-0"><i class="bi bi-info-circle me-1"></i>
                            This is one of the original website pages. Its texts, pictures and lists are edited with <a href="{{ route('admin.pages.content', $page) }}"><strong>Edit page content</strong></a>.
                            The address (<code>{{ $page->slug === 'index' ? '/' : '/' . $page->slug . '.php' }}</code>) is fixed so existing links keep working.
                        </div>
                    @endunless
                </div>
            </div>

            <div class="adm-card">
                <div class="adm-card__head"><h2>Search engines (SEO)</h2></div>
                <div class="adm-card__body">
                    <x-admin.field name="meta_title" label="Browser / search title" :value="$page->meta_title" placeholder="{{ $page->title ?: 'Leave empty to use the page title' }}" help="Aim for 50–60 characters. Shown in the browser tab and as the headline in Google." :attrs="['maxlength' => 160]" />
                    <x-admin.field name="meta_description" type="textarea" :rows="3" label="Meta description" :value="$page->meta_description" help="About 150 characters. Shown under the title in search results." :attrs="['maxlength' => 300]" />
                    <x-admin.field name="meta_keywords" label="Keywords" :value="$page->meta_keywords" help="Optional, comma separated." :attrs="['maxlength' => 300]" />
                    <x-admin.image name="og_image" label="Social sharing image" :current="$page->og_image" help="Shown when the page is shared (1200×630 px works best)." />
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="adm-card">
                <div class="adm-card__head"><h2>Publishing</h2></div>
                <div class="adm-card__body">
                    <label class="form-label" for="status">Visibility</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach(\App\Models\Page::STATUSES as $v => $l)
                            <option value="{{ $v }}" @selected(old('status', $page->status) === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Drafts are hidden from visitors. Signed-in admins can still preview them.</div>
                    @if($editing)
                        <hr>
                        <dl class="small mb-0">
                            <dt class="text-muted fw-semibold">Last updated</dt><dd>{{ $page->updated_at?->format('d M Y, H:i') }}@if($page->editor) by {{ $page->editor->name }}@endif</dd>
                            <dt class="text-muted fw-semibold">Published</dt><dd class="mb-0">{{ $page->published_at?->format('d M Y, H:i') ?? 'Not yet' }}</dd>
                        </dl>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="adm-formbar">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i> {{ $editing ? 'Save changes' : 'Create page' }}</button>
        <a class="btn btn-light" href="{{ route('admin.pages.index') }}">Cancel</a>
    </div>
</form>
@endsection
