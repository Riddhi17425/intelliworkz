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

<div class="adm-card">
    <form class="adm-filters" method="get">
        <div class="adm-search"><i class="bi bi-search"></i><input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search title or address…"></div>
        <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach(\App\Models\Page::STATUSES as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach
        </select>
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
                            <div class="small text-muted">@if($p->is_system)<i class="bi bi-lock-fill me-1" title="Original website page"></i>Website page @else <i class="bi bi-file-text me-1"></i>Custom page @endif</div>
                        </td>
                        <td class="d-none d-md-table-cell small"><a class="text-muted" target="_blank" rel="noopener" href="{{ $p->url() }}">/{{ $p->slug === 'index' ? '' : $p->slug . ($p->is_system ? '.php' : '') }}</a></td>
                        <td><x-admin.badge :status="$p->status"/></td>
                        <td class="small text-muted d-none d-lg-table-cell">{{ $p->updated_at?->diffForHumans() }}</td>
                        <td class="text-end text-nowrap">
                            @if($p->is_system)<a class="btn btn-sm btn-primary" href="{{ route('admin.pages.content', $p) }}"><i class="bi bi-pencil-square me-1"></i>Edit content</a>@endif
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.pages.edit', $p) }}" title="SEO & settings"><i class="bi bi-gear"></i></a>
                            <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="{{ $p->url() }}" title="View"><i class="bi bi-eye"></i></a>
                            @unless($p->slug === 'index' && $p->isPublished())
                                <form class="d-inline" method="post" action="{{ route('admin.pages.status', $p) }}">@csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-secondary" title="{{ $p->isPublished() ? 'Unpublish' : 'Publish' }}"><i class="bi {{ $p->isPublished() ? 'bi-eye-slash' : 'bi-cloud-arrow-up' }}"></i></button>
                                </form>
                            @endunless
                            @if(!$p->is_system)
                                @can('delete-content')
                                    <x-admin.delete :action="route('admin.pages.destroy', $p)" :icon-only="true" label="Delete page" title="Delete “{{ $p->title }}”?" message="The page and its address will be removed from the website." />
                                @endcan
                            @endif
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
