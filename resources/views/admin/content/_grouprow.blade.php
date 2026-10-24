{{-- One item inside a column of a grouped list. "_orig" = "column.item" it was loaded from (empty when new). --}}
<div class="border rounded-3 p-3 mb-3 bg-light" data-sub>
    <input type="hidden" name="{{ $prefix }}[_orig]" value="{{ $orig }}">
    <div class="d-flex align-items-center gap-2 mb-2">
        <div class="small fw-semibold text-muted">{{ $title }}</div>
        @if($schema->canRemove())
            <button type="button" class="btn btn-sm btn-light text-danger ms-auto" data-act="remove-sub" title="Remove this item"><i class="bi bi-x-lg"></i></button>
        @endif
    </div>
    @include('admin.content._rows', ['defs' => $schema->fields(), 'prefix' => $prefix, 'item' => $row])
</div>
