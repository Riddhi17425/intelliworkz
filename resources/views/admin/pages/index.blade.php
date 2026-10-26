@extends('admin.layouts.app')
@section('title', 'Pages')

@php
    $sortLink = function (string $col, string $label) use ($sort, $dir) {
        $next = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
        $url = request()->fullUrlWithQuery(['sort' => $col, 'dir' => $next, 'page' => null]);
        $icon = $sort === $col ? ($dir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill') : 'bi-chevron-expand';
        return new \Illuminate\Support\HtmlString('<a class="sortable ' . ($sort === $col ? 'is-sorted' : '') . '" href="' . e($url) . '">' . e($label) . ' <i class="bi ' . $icon . '"></i></a>');
    };
@endphp

@section('content')
<x-admin.head title="Pages" subtitle="Every page of the website. Edit its content, SEO and whether visitors can see it.">
    <x-slot:actions><a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> New page</a></x-slot:actions>
</x-admin.head>

@php
    $cntAll = \App\Models\Page::count();
    $cntPub = \App\Models\Page::where('status', \App\Models\Page::STATUS_PUBLISHED)->count();
    $cntDraft = \App\Models\Page::where('status', \App\Models\Page::STATUS_DRAFT)->count();
    $st = request('status');
@endphp
<div class="adm-card">
    <div class="adm-pills" role="tablist" aria-label="Filter by status">
        <a class="adm-pill {{ !$st ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}">All <span>{{ $cntAll }}</span></a>
        <a class="adm-pill {{ $st === 'published' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['status' => 'published', 'page' => null]) }}">Published <span>{{ $cntPub }}</span></a>
        <a class="adm-pill {{ $st === 'draft' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['status' => 'draft', 'page' => null]) }}">Draft <span>{{ $cntDraft }}</span></a>
    </div>
    <form class="adm-filters" method="get">
        <div class="adm-search"><i class="bi bi-search"></i><input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search title or address…"></div>
        @if($st)<input type="hidden" name="status" value="{{ $st }}">@endif
        <select class="form-select" name="type" onchange="this.form.submit()">
            <option value="">All pages</option>
            <option value="system" @selected(request('type') === 'system')>Website pages</option>
            <option value="custom" @selected(request('type') === 'custom')>Pages I created</option>
        </select>
        <button class="btn btn-sm btn-primary">Filter</button>
        @if(request()->hasAny(['q', 'status', 'type']))<a class="btn btn-sm btn-light" href="{{ route('admin.pages.index') }}">Reset</a>@endif
    </form>

    @if($pages->isEmpty())
        <x-admin.empty icon="bi-file-earmark" title="No pages match" text="Try another search or reset the filters."/>
    @else
        <div class="table-responsive">
            <table class="table adm-table table-hover">
                <thead><tr>
                    <th>{{ $sortLink('title', 'Page') }}</th>
                    <th class="d-none d-md-table-cell">Address</th>
                    <th>{{ $sortLink('status', 'Status') }}</th>
                    <th class="d-none d-lg-table-cell">{{ $sortLink('updated_at', 'Updated') }}</th>
                    <th class="text-end">Actions</th>
                </tr></thead>
                <tbody>
                @foreach($pages as $p)
                    <tr>
                        <td>
                            <a class="row-title" href="{{ route('admin.pages.edit', $p) }}">{{ $p->title }}</a>
                            <div class="small text-muted">@if($p->is_system)Website page @elseif($p->hasEditor())<i class="bi bi-files me-1"></i>Copy of design: {{ $p->viewKey() }} @else <i class="bi bi-file-text me-1"></i>Custom page @endif</div>
                        </td>
                        <td class="d-none d-md-table-cell small"><a class="text-muted" target="_blank" rel="noopener" href="{{ $p->url() }}">/{{ $p->slug === 'index' ? '' : $p->slug . ($p->is_system ? '.php' : '') }}</a></td>
                        <td><x-admin.badge :status="$p->status"/></td>
                        <td class="small text-muted d-none d-lg-table-cell">{{ $p->updated_at?->diffForHumans() }}</td>
                        <td class="text-end text-nowrap">
                            @if($p->hasEditor())<a class="btn btn-sm btn-primary" href="{{ route('admin.pages.content', $p) }}"><i class="bi bi-pencil-square me-1"></i>Edit content</a>@else<a class="btn btn-sm btn-primary" href="{{ route('admin.pages.edit', $p) }}"><i class="bi bi-pencil-square me-1"></i>Edit</a>@endif
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-outline-secondary adm-more" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" aria-label="More actions for {{ $p->title }}"><i class="bi bi-three-dots"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 adm-menu">
                                    <li><a class="dropdown-item rounded" target="_blank" rel="noopener" href="{{ $p->url() }}"><i class="bi bi-box-arrow-up-right me-2"></i>View live page</a></li>
                                    <li><a class="dropdown-item rounded" href="{{ route('admin.pages.edit', $p) }}"><i class="bi bi-gear me-2"></i>SEO &amp; settings</a></li>
                                    @if($p->hasEditor() && $p->slug !== 'index')<li><a class="dropdown-item rounded" href="{{ route('admin.pages.create', ['from' => $p->slug]) }}"><i class="bi bi-files me-2"></i>New page with this design</a></li>@endif
                                    @unless($p->slug === 'index' && $p->isPublished())
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="post" action="{{ route('admin.pages.status', $p) }}">@csrf @method('PATCH')
                                                <button class="dropdown-item rounded"><i class="bi {{ $p->isPublished() ? 'bi-eye-slash' : 'bi-cloud-arrow-up' }} me-2"></i>{{ $p->isPublished() ? 'Unpublish (make draft)' : 'Publish' }}</button>
                                            </form>
                                        </li>
                                    @endunless
                                    @if(!$p->is_system)
                                        @can('delete-content')
                                            <li><x-admin.delete :action="route('admin.pages.destroy', $p)" class="dropdown-item rounded text-danger" label="Delete page" title="Delete “{{ $p->title }}”?" message="The page and its address will be removed from the website." /></li>
                                        @endcan
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="adm-card__foot">Showing {{ $pages->firstItem() }}–{{ $pages->lastItem() }} of {{ $pages->total() }}{{ $pages->links() }}</div>
    @endif
</div>
@endsection
