@php
    $kind = $schema->kind();
    $items = $list->items ?? [];
    $base = "lists[{$list->id}]";
    $defs = $schema->fields();
    $count = count($items);
    $addLabel = match ($kind) { 'groups' => 'Add ' . strtolower($schema->groupLabel()), 'map' => 'Add tab', default => 'Add item' };
@endphp
<div class="adm-fieldrow" data-list data-kind="{{ $kind }}" id="list-{{ $list->id }}" @if($schema->maxItems()) data-max="{{ $schema->maxItems() }}" @endif>
    <input type="hidden" name="{{ $base }}[present]" value="1">
    <div class="d-flex align-items-center gap-2 mb-2">
        <label class="form-label mb-0">{{ $schema->label() }}<span class="adm-type">List · <span data-count>{{ $count }}</span>@if($schema->maxItems()) / {{ $schema->maxItems() }} max @endif</span></label>
        @if($schema->canAdd())
            <button type="button" class="btn btn-sm btn-outline-primary ms-auto" data-act="add-item"><i class="bi bi-plus-lg me-1"></i> {{ $addLabel }}</button>
        @endif
    </div>
    @if(!$schema->canAdd() && !$schema->canRemove())
        <div class="form-text mt-0 mb-2"><i class="bi bi-lock me-1"></i>This part of the design is drawn at fixed positions, so its items can be edited here but not added or removed.</div>
    @endif

    <div class="adm-list" data-items>
        @switch($kind)
            @case('strings')
                @foreach($items as $i => $text)
                    @include('admin.content._string', ['name' => "{$base}[items][{$i}]", 'value' => $text, 'schema' => $schema])
                @endforeach
                @break

            @case('map')
                @foreach($items as $key => $label)
                    @include('admin.content._maprow', ['name' => "{$base}[map][{$key}]", 'key' => $key, 'value' => $label, 'schema' => $schema])
                @endforeach
                @break

            @case('maprows')
                @foreach($items as $key => $row)
                    @include('admin.content._mapitem', ['schema' => $schema, 'defs' => $defs, 'prefix' => "{$base}[map][{$key}]", 'item' => $row, 'title' => $schema->itemTitle($row, $key)])
                @endforeach
                @break

            @case('groups')
                @foreach($items as $g => $rows)
                    @include('admin.content._group', ['schema' => $schema, 'defs' => $defs, 'prefix' => "{$base}[groups][{$g}]", 'rows' => $rows, 'g' => $g])
                @endforeach
                @break

            @default
                @foreach($items as $i => $row)
                    @include('admin.content._item', ['schema' => $schema, 'defs' => $defs, 'prefix' => "{$base}[items][{$i}]", 'item' => $row, 'orig' => $i, 'index' => $i])
                @endforeach
        @endswitch
    </div>

    @if($schema->canAdd())
        <template data-template>
            @switch($kind)
                @case('strings')
                    @include('admin.content._string', ['name' => "{$base}[items][__N__]", 'value' => '', 'schema' => $schema])
                    @break
                @case('map')
                    @include('admin.content._maprow', ['name' => "{$base}[new][__N__]", 'key' => 'new', 'value' => '', 'schema' => $schema])
                    @break
                @case('maprows')
                    @include('admin.content._mapitem', ['schema' => $schema, 'defs' => $defs, 'prefix' => "{$base}[new][__N__]", 'item' => $schema->blank(), 'title' => 'New item', 'fresh' => true])
                    @break
                @case('groups')
                    @include('admin.content._group', ['schema' => $schema, 'defs' => $defs, 'prefix' => "{$base}[groups][__N__]", 'rows' => [$schema->blank()], 'g' => null])
                    @break
                @default
                    @include('admin.content._item', ['schema' => $schema, 'defs' => $defs, 'prefix' => "{$base}[items][__N__]", 'item' => $schema->blank(), 'orig' => '', 'index' => 0, 'fresh' => true])
            @endswitch
        </template>
    @endif
</div>
