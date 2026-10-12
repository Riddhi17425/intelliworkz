@extends('admin.layouts.app')
@section('title', 'Contact inquiries')

@php
    $sortLink = function (string $col, string $label) use ($sort, $dir) {
        $next = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
        $url = request()->fullUrlWithQuery(['sort' => $col, 'dir' => $next, 'page' => null]);
        $icon = $sort === $col ? ($dir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill') : 'bi-chevron-expand';
        return new \Illuminate\Support\HtmlString('<a class="sortable ' . ($sort === $col ? 'is-sorted' : '') . '" href="' . e($url) . '">' . e($label) . ' <i class="bi ' . $icon . '"></i></a>');
    };
@endphp

@section('content')
<x-admin.head title="Contact inquiries" :subtitle="$totals['all'] . ' received · ' . $totals['unread'] . ' unread'">
    <x-slot:actions>
        <a class="btn btn-outline-secondary" href="{{ route('admin.inquiries.export', request()->query()) }}"><i class="bi bi-download me-1"></i> Export CSV</a>
    </x-slot:actions>
</x-admin.head>

<form method="post" action="{{ route('admin.inquiries.bulk') }}" id="bulkForm">@csrf
<div class="adm-card">
    <div class="adm-filters" role="search">
        <div class="adm-search"><i class="bi bi-search"></i><input class="form-control" form="filterForm" type="search" name="q" value="{{ request('q') }}" placeholder="Search name, e-mail, message…"></div>
        <select class="form-select" form="filterForm" name="status" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="unread" @selected(request('status') === 'unread')>Unread</option>
            <option value="read" @selected(request('status') === 'read')>Read</option>
        </select>
        <select class="form-select" form="filterForm" name="source" onchange="this.form.submit()">
            <option value="">All pages</option>
            @foreach($sources as $s)<option value="{{ $s }}" @selected(request('source') === $s)>{{ $s }}</option>@endforeach
        </select>
        <input class="form-control" form="filterForm" type="date" name="from" value="{{ request('from') }}" title="From date" aria-label="From date">
        <input class="form-control" form="filterForm" type="date" name="to" value="{{ request('to') }}" title="To date" aria-label="To date">
        <button class="btn btn-sm btn-primary" form="filterForm">Filter</button>
        @if(request()->hasAny(['q', 'status', 'source', 'from', 'to']))<a class="btn btn-sm btn-light" href="{{ route('admin.inquiries.index') }}">Reset</a>@endif
    </div>

    @if($inquiries->isEmpty())
        <x-admin.empty icon="bi-envelope-open" title="{{ $totals['all'] ? 'No inquiries match' : 'No inquiries yet' }}" text="{{ $totals['all'] ? 'Try different filters.' : 'Messages sent from the website forms will appear here.' }}"/>
    @else
        <div class="d-flex flex-wrap align-items-center gap-2 px-3 py-2 border-bottom bg-light">
            <span class="small text-muted me-1">With selected:</span>
            <button name="action" value="read" class="btn btn-sm btn-outline-secondary"><i class="bi bi-envelope-open me-1"></i>Mark read</button>
            <button name="action" value="unread" class="btn btn-sm btn-outline-secondary"><i class="bi bi-envelope me-1"></i>Mark unread</button>
            @can('delete-content')
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#bulkDeleteModal"><i class="bi bi-trash3 me-1"></i>Delete</button>
            @endcan
            @error('ids')<span class="text-danger small ms-2">{{ $message }}</span>@enderror
        </div>
        <div class="table-responsive">
            <table class="table adm-table table-hover">
                <thead><tr>
                    <th style="width:40px"><input class="form-check-input" type="checkbox" id="checkAll" aria-label="Select all"></th>
                    <th>{{ $sortLink('name', 'From') }}</th>
                    <th class="d-none d-md-table-cell">Message</th>
                    <th class="d-none d-lg-table-cell">{{ $sortLink('source', 'Page') }}</th>
                    <th>{{ $sortLink('created_at', 'Received') }}</th>
                    <th class="text-end">Actions</th>
                </tr></thead>
                <tbody>
                @foreach($inquiries as $i)
                    <tr class="{{ $i->is_read ? '' : 'is-unread' }}">
                        <td><input class="form-check-input row-check" type="checkbox" name="ids[]" value="{{ $i->id }}" aria-label="Select inquiry from {{ $i->name }}"></td>
                        <td>
                            <a class="row-title" href="{{ route('admin.inquiries.show', $i) }}">@unless($i->is_read)<span class="adm-dot" title="Unread"></span>@endunless{{ $i->name }}</a>
                            <div class="small text-muted">{{ $i->email }}@if($i->company) · {{ $i->company }}@endif</div>
                        </td>
                        <td class="d-none d-md-table-cell small" style="max-width: 340px"><div class="text-truncate">@if($i->subject)<strong>{{ $i->subject }}</strong> — @endif{{ $i->message ?: '—' }}</div></td>
                        <td class="d-none d-lg-table-cell small text-muted">{{ $i->source ?: '—' }}</td>
                        <td class="small text-muted text-nowrap"><span title="{{ $i->created_at->format('d M Y, H:i') }}">{{ $i->created_at->diffForHumans() }}</span></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.inquiries.show', $i) }}" title="View"><i class="bi bi-eye"></i></a>
                            @can('delete-content')
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteModal" data-action="{{ route('admin.inquiries.destroy', $i) }}" data-name="{{ $i->name }}"><i class="bi bi-trash3"></i></button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="adm-card__foot">Showing {{ $inquiries->firstItem() }}–{{ $inquiries->lastItem() }} of {{ $inquiries->total() }}{{ $inquiries->links() }}</div>
    @endif
</div>
</form>
<form id="filterForm" method="get"></form>

@can('delete-content')
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg"><div class="modal-body p-4 text-center">
    <div class="adm-stat__icon tone-red mx-auto mb-3"><i class="bi bi-trash3"></i></div>
    <h5 class="fw-bold">Delete the selected inquiries?</h5><p class="text-muted">This cannot be undone.</p>
    <div class="d-flex justify-content-center gap-2"><button class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" form="bulkForm" name="action" value="delete">Yes, delete</button></div>
</div></div></div></div>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><form class="modal-content border-0 shadow-lg" method="post" action="#">@csrf @method('DELETE')<div class="modal-body p-4 text-center">
    <div class="adm-stat__icon tone-red mx-auto mb-3"><i class="bi bi-trash3"></i></div>
    <h5 class="fw-bold">Delete this inquiry?</h5><p class="text-muted">The message from <strong data-name></strong> will be permanently removed.</p>
    <div class="d-flex justify-content-center gap-2"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Yes, delete</button></div>
</div></form></div></div>
@endcan
@endsection

@push('scripts')
<script>
(function () {
    var all = document.getElementById('checkAll');
    if (all) all.addEventListener('change', function () { document.querySelectorAll('.row-check').forEach(function (c) { c.checked = all.checked; }); });
    var dm = document.getElementById('deleteModal');
    if (dm) dm.addEventListener('show.bs.modal', function (e) {
        dm.querySelector('form').action = e.relatedTarget.dataset.action;
        dm.querySelector('[data-name]').textContent = e.relatedTarget.dataset.name;
    });
})();
</script>
@endpush
