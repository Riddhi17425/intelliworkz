{{-- One column of a grouped list (e.g. a column of tools). $g = its position, null for a new column. --}}
@php
    $isNew = $g === null;
    $last = \Illuminate\Support\Arr::last($rows);
@endphp
<div class="adm-item is-open" data-item>
    <div class="adm-item__head" data-act="toggle">
        <span class="adm-item__no" data-no>{{ $isNew ? '+' : $g + 1 }}</span>
        <span class="adm-item__title">{{ $isNew ? 'New ' . strtolower($schema->groupLabel()) : $schema->groupLabel() . ' ' . ($g + 1) }}</span>
        <div class="adm-item__tools">
            @if($schema->canReorder())
                <button type="button" class="btn btn-sm btn-light" data-act="up" title="Move up"><i class="bi bi-arrow-up"></i></button>
                <button type="button" class="btn btn-sm btn-light" data-act="down" title="Move down"><i class="bi bi-arrow-down"></i></button>
            @endif
            @if($schema->canRemove())
                <button type="button" class="btn btn-sm btn-light text-danger" data-act="remove" title="Remove this {{ strtolower($schema->groupLabel()) }} and everything in it"><i class="bi bi-trash3"></i></button>
            @endif
        </div>
        <i class="bi bi-chevron-down adm-item__chev"></i>
    </div>
    <div class="adm-item__body" data-repeater>
        <div data-rows>
            @foreach($rows as $r => $row)
                @include('admin.content._grouprow', ['prefix' => "{$prefix}[{$r}]", 'row' => $row, 'orig' => $isNew ? '' : "{$g}.{$r}", 'title' => $isNew ? 'New item' : $schema->itemTitle($row, $r)])
            @endforeach
        </div>
        @if($schema->canAdd())
            <template data-sub-template>
                @include('admin.content._grouprow', ['prefix' => "{$prefix}[__M__]", 'row' => $schema->blank(is_array($last) ? $last : null), 'orig' => '', 'title' => 'New item'])
            </template>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-act="add-sub"><i class="bi bi-plus-lg me-1"></i> Add item to this {{ strtolower($schema->groupLabel()) }}</button>
        @endif
    </div>
</div>
