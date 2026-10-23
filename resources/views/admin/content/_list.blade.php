@php
    $kind = $schema->kind();
    $items = $list->items ?? [];
    $base = "lists[{$list->id}]";
    $defs = $schema->fields();
    $count = count($items);
@endphp
<div class="adm-fieldrow" data-list data-kind="{{ $kind }}" id="list-{{ $list->id }}" @if($schema->maxItems()) data-max="{{ $schema->maxItems() }}" @endif>
    <input type="hidden" name="{{ $base }}[present]" value="1">
    <div class="d-flex align-items-center gap-2 mb-2">
        <label class="form-label mb-0">{{ $schema->label() }}<span class="adm-type">List · <span data-count>{{ $count }}</span>@if($schema->maxItems()) / {{ $schema->maxItems() }} max @endif</span></label>
        @if($schema->canAdd())
            <button type="button" class="btn btn-sm btn-outline-primary ms-auto" data-act="add-item"><i class="bi bi-plus-lg me-1"></i> Add item</button>
        @endif
    </div>

    <div class="adm-list" data-items>
        @switch($kind)
            @case('strings')
                @foreach($items as $i => $text)
                    @include('admin.content._string', ['name' => "{$base}[items][{$i}]", 'value' => $text, 'schema' => $schema])
                @endforeach
                @break

            @case('map')
                @foreach($items as $key => $label)
                    <div class="input-group">
                        <span class="input-group-text small text-muted">{{ $key }}</span>
                        <input type="text" class="form-control" name="{{ $base }}[map][{{ $key }}]" value="{{ \App\Support\Html::forInput($label) }}" maxlength="200">
                    </div>
                @endforeach
                @break

            @case('maprows')
                @foreach($items as $key => $row)
                    <div class="adm-item is-open">
                        <div class="adm-item__head" data-act="toggle">
                            <span class="adm-item__no"><i class="bi bi-geo-alt"></i></span>
                            <span class="adm-item__title">{{ $schema->itemTitle($row, $key) }}</span>
                            <i class="bi bi-chevron-down adm-item__chev"></i>
                        </div>
                        <div class="adm-item__body">
                            @include('admin.content._rows', ['defs' => $defs, 'prefix' => "{$base}[map][{$key}]", 'item' => $row, 'orig' => null])
                        </div>
                    </div>
                @endforeach
                @break

            @case('groups')
                @foreach($items as $g => $rows)
                    <div class="adm-item is-open">
                        <div class="adm-item__head" data-act="toggle">
                            <span class="adm-item__no">{{ $g + 1 }}</span>
                            <span class="adm-item__title">{{ $schema->groupLabel() }} {{ $g + 1 }}</span>
                            <i class="bi bi-chevron-down adm-item__chev"></i>
                        </div>
                        <div class="adm-item__body">
                            @foreach($rows as $r => $row)
                                <div class="border rounded-3 p-3 mb-3 bg-light">
                                    <div class="small fw-semibold text-muted mb-2">{{ $schema->itemTitle($row, $r) }}</div>
                                    @include('admin.content._rows', ['defs' => $defs, 'prefix' => "{$base}[groups][{$g}][{$r}]", 'item' => $row, 'orig' => null])
                                </div>
                            @endforeach
                        </div>
                    </div>
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
            @if($kind === 'strings')
                @include('admin.content._string', ['name' => "{$base}[items][__N__]", 'value' => '', 'schema' => $schema])
            @else
                @include('admin.content._item', ['schema' => $schema, 'defs' => $defs, 'prefix' => "{$base}[items][__N__]", 'item' => $schema->blank(), 'orig' => '', 'index' => 0, 'fresh' => true])
            @endif
        </template>
    @endif
</div>
