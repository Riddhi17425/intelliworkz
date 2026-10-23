<div class="adm-item {{ !empty($fresh) ? 'is-open' : '' }}" data-item>
    <input type="hidden" name="{{ $prefix }}[_orig]" value="{{ $orig }}">
    <div class="adm-item__head" data-act="toggle">
        <span class="adm-item__no" data-no>{{ is_int($index) ? $index + 1 : '+' }}</span>
        <span class="adm-item__title" data-title>{{ !empty($fresh) ? 'New item' : $schema->itemTitle($item, $index) }}</span>
        <div class="adm-item__tools">
            @if($schema->canReorder())
                <button type="button" class="btn btn-sm btn-light" data-act="up" title="Move up"><i class="bi bi-arrow-up"></i></button>
                <button type="button" class="btn btn-sm btn-light" data-act="down" title="Move down"><i class="bi bi-arrow-down"></i></button>
            @endif
            @if($schema->canRemove())
                <button type="button" class="btn btn-sm btn-light text-danger" data-act="remove" title="Remove this item"><i class="bi bi-trash3"></i></button>
            @endif
        </div>
        <i class="bi bi-chevron-down adm-item__chev"></i>
    </div>
    <div class="adm-item__body">
        @include('admin.content._rows', ['defs' => $defs, 'prefix' => $prefix, 'item' => $item, 'titleKey' => config('content_lists.' . $schema->key . '.title')])
    </div>
</div>
