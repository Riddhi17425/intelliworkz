@extends('admin.layouts.app')
@section('title', 'Services & menus')

@section('content')
<x-admin.head title="Services & menus" subtitle="The “Services” menu under the logo and the link columns in the footer.">
    <x-slot:actions>
        @if($tab === 'header')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#groupAddModal"><i class="bi bi-plus-lg me-1"></i> Add service group</button>
        @endif
    </x-slot:actions>
</x-admin.head>

<nav class="adm-tabs">
    <a class="{{ $tab === 'header' ? 'active' : '' }}" href="{{ route('admin.menu.index', ['tab' => 'header']) }}"><i class="bi bi-menu-button-wide me-1"></i> Services menu ({{ $counts['header'] }} groups)</a>
    <a class="{{ $tab === 'footer' ? 'active' : '' }}" href="{{ route('admin.menu.index', ['tab' => 'footer']) }}"><i class="bi bi-layout-text-sidebar-reverse me-1"></i> Footer columns ({{ $counts['footer'] }})</a>
</nav>

@if($tab === 'footer')
    <div class="alert alert-light border small"><i class="bi bi-info-circle me-1"></i> The footer has six fixed columns. You can rename them and add, edit, reorder or hide their links.</div>
@endif

@forelse($groups as $g)
    <div class="adm-card mb-3" id="group-{{ $g->id }}">
        <div class="adm-card__head flex-wrap">
            <h2>{{ $g->title }}</h2>
            @if($tab === 'header')<x-admin.badge :status="$g->is_published ? 'published' : 'draft'">{{ $g->is_published ? 'Visible' : 'Hidden' }}</x-admin.badge>@endif
            <span class="text-muted small">{{ $g->items->count() }} {{ \Illuminate\Support\Str::plural('link', $g->items->count()) }}</span>
            <div class="ms-auto d-flex flex-wrap gap-1">
                <form method="post" action="{{ route('admin.menu.groups.move', $g) }}" class="d-inline">@csrf<input type="hidden" name="dir" value="up"><button class="btn btn-sm btn-light" title="Move up" @disabled($loop->first)><i class="bi bi-arrow-up"></i></button></form>
                <form method="post" action="{{ route('admin.menu.groups.move', $g) }}" class="d-inline">@csrf<input type="hidden" name="dir" value="down"><button class="btn btn-sm btn-light" title="Move down" @disabled($loop->last)><i class="bi bi-arrow-down"></i></button></form>
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#groupEditModal"
                        data-action="{{ route('admin.menu.groups.update', $g) }}" data-title="{{ $g->title }}" data-published="{{ $g->is_published ? 1 : 0 }}" data-header="{{ $tab === 'header' ? 1 : 0 }}"><i class="bi bi-pencil me-1"></i>Rename</button>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal"
                        data-mode="add" data-action="{{ route('admin.menu.items.store', $g) }}" data-group="{{ $g->title }}"><i class="bi bi-plus-lg me-1"></i>Add link</button>
                @if($tab === 'header')
                    @can('delete-content')
                        <x-admin.delete :action="route('admin.menu.groups.destroy', $g)" label="Delete group" title="Delete “{{ $g->title }}”?" message="The group and all {{ $g->items->count() }} links in it will be removed from the menu." />
                    @endcan
                @endif
            </div>
        </div>

        @if($g->items->isEmpty())
            <x-admin.empty icon="bi-link-45deg" title="No links yet" text="Use “Add link” to put the first one here."/>
        @else
            <div class="table-responsive">
                <table class="table adm-table table-hover">
                    <thead><tr><th>Link text</th><th>Goes to</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    @foreach($g->items as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->label }}@if($item->new_tab) <i class="bi bi-box-arrow-up-right small text-muted" title="Opens in a new tab"></i>@endif</td>
                            <td class="small">
                                @if($item->page)<span class="adm-badge off plain"><i class="bi bi-file-earmark me-1"></i>{{ $item->page->title }}</span>
                                @elseif($item->url === '#' || $item->url === '')<span class="text-muted">Not linked yet (#)</span>
                                @else<span class="text-muted">{{ \Illuminate\Support\Str::limit($item->url, 50) }}</span>@endif
                            </td>
                            <td><x-admin.badge :status="$item->is_published ? 'published' : 'draft'">{{ $item->is_published ? 'Visible' : 'Hidden' }}</x-admin.badge></td>
                            <td class="text-end text-nowrap">
                                <form method="post" action="{{ route('admin.menu.items.move', $item) }}" class="d-inline">@csrf<input type="hidden" name="dir" value="up"><button class="btn btn-sm btn-light" title="Move up" @disabled($loop->first)><i class="bi bi-arrow-up"></i></button></form>
                                <form method="post" action="{{ route('admin.menu.items.move', $item) }}" class="d-inline">@csrf<input type="hidden" name="dir" value="down"><button class="btn btn-sm btn-light" title="Move down" @disabled($loop->last)><i class="bi bi-arrow-down"></i></button></form>
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#itemModal" title="Edit"
                                        data-mode="edit" data-action="{{ route('admin.menu.items.update', $item) }}" data-group="{{ $g->title }}"
                                        data-label="{{ $item->label }}" data-url="{{ $item->url === '#' ? '' : $item->url }}" data-page="{{ $item->page_id }}"
                                        data-published="{{ $item->is_published ? 1 : 0 }}" data-newtab="{{ $item->new_tab ? 1 : 0 }}"><i class="bi bi-pencil"></i></button>
                                @can('delete-content')
                                    <x-admin.delete :action="route('admin.menu.items.destroy', $item)" :icon-only="true" label="Delete link" title="Delete “{{ $item->label }}”?" message="This link will be removed from the menu." />
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@empty
    <div class="adm-card"><x-admin.empty icon="bi-diagram-3" title="No groups yet" text="Add the first service group."/></div>
@endforelse

{{-- add group --}}
<div class="modal fade" id="groupAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><form class="modal-content border-0 shadow-lg" method="post" action="{{ route('admin.menu.groups.store') }}">@csrf
        <div class="modal-header border-0"><h5 class="modal-title fw-bold">Add service group</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><x-admin.field name="title" label="Group name" :required="true" placeholder="e.g. Cloud & DevOps" :attrs="['maxlength' => 80]" /></div>
        <div class="modal-footer border-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Add group</button></div>
    </form></div>
</div>

{{-- rename group --}}
<div class="modal fade" id="groupEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><form class="modal-content border-0 shadow-lg" method="post" action="#">@csrf @method('PUT')
        <div class="modal-header border-0"><h5 class="modal-title fw-bold">Edit group</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <label class="form-label" for="ge_title">Group name<span class="req">*</span></label>
            <input id="ge_title" class="form-control mb-3" name="title" maxlength="80" required>
            <div class="form-check form-switch" data-published-wrap>
                <input type="hidden" name="is_published" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="ge_pub" name="is_published" value="1">
                <label class="form-check-label" for="ge_pub">Show this group in the menu</label>
            </div>
        </div>
        <div class="modal-footer border-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form></div>
</div>

{{-- add / edit link --}}
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><form class="modal-content border-0 shadow-lg" method="post" action="#">@csrf <input type="hidden" name="_method" value="PUT" disabled data-method>
        <div class="modal-header border-0"><div><h5 class="modal-title fw-bold" data-title>Add link</h5><div class="small text-muted" data-sub></div></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <label class="form-label" for="it_label">Link text<span class="req">*</span></label>
            <input id="it_label" class="form-control mb-3" name="label" maxlength="120" required placeholder="e.g. Video Editing">
            <label class="form-label" for="it_page">Link to a page on this website</label>
            <select id="it_page" class="form-select mb-3" name="page_id">
                <option value="">— none, use a custom address below —</option>
                @foreach($pages as $p)<option value="{{ $p->id }}">{{ $p->title }}{{ $p->status === 'published' ? '' : ' (draft)' }}</option>@endforeach
            </select>
            <label class="form-label" for="it_url">…or a custom address</label>
            <input id="it_url" class="form-control mb-1" name="url" maxlength="500" placeholder="https://example.com or contact-us.php">
            <div class="form-text mb-3">Ignored when a page is chosen above. Leave both empty for a link that does not go anywhere yet.</div>
            <div class="d-flex flex-wrap gap-4">
                <div class="form-check form-switch"><input type="hidden" name="is_published" value="0"><input class="form-check-input" type="checkbox" role="switch" id="it_pub" name="is_published" value="1" checked><label class="form-check-label" for="it_pub">Visible</label></div>
                <div class="form-check form-switch"><input type="hidden" name="new_tab" value="0"><input class="form-check-input" type="checkbox" role="switch" id="it_tab" name="new_tab" value="1"><label class="form-check-label" for="it_tab">Open in a new tab</label></div>
            </div>
        </div>
        <div class="modal-footer border-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" data-submit>Save link</button></div>
    </form></div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var ge = document.getElementById('groupEditModal');
    ge.addEventListener('show.bs.modal', function (e) {
        var b = e.relatedTarget, f = ge.querySelector('form');
        f.action = b.dataset.action;
        f.querySelector('[name=title]').value = b.dataset.title;
        f.querySelector('[data-published-wrap]').style.display = b.dataset.header === '1' ? '' : 'none';
        f.querySelector('#ge_pub').checked = b.dataset.published === '1';
    });
    var im = document.getElementById('itemModal');
    im.addEventListener('show.bs.modal', function (e) {
        var b = e.relatedTarget, f = im.querySelector('form'), edit = b.dataset.mode === 'edit';
        f.action = b.dataset.action;
        f.querySelector('[data-method]').disabled = !edit;
        im.querySelector('[data-title]').textContent = edit ? 'Edit link' : 'Add link';
        im.querySelector('[data-sub]').textContent = 'In “' + b.dataset.group + '”';
        f.querySelector('[name=label]').value = edit ? b.dataset.label : '';
        f.querySelector('[name=url]').value = edit ? b.dataset.url : '';
        f.querySelector('[name=page_id]').value = edit ? (b.dataset.page || '') : '';
        f.querySelector('#it_pub').checked = edit ? b.dataset.published === '1' : true;
        f.querySelector('#it_tab').checked = edit ? b.dataset.newtab === '1' : false;
    });
})();
</script>
@endpush
