{{-- The editable fields of one row. $defs = field definitions, $prefix = form-name prefix, $item = current values --}}
@foreach($defs as $key => $def)
    @php
        $name = $prefix . '[' . $key . ']';
        $val = is_array($item) ? ($item[$key] ?? null) : null;
        $fid = 'r_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
        $isTitle = isset($titleKey) && (string) $titleKey === (string) $key;
    @endphp
    <div class="mb-3">
        @if($def['type'] === 'image')
            <x-admin.image :name="$name . '__file'" :remove-name="$name . '__remove'" :current="is_string($val) ? $val : null" :label="$def['label']" :compact="true" />
        @else
            <label class="form-label" for="{{ $fid }}">{{ $def['label'] }}@if($def['type'] === 'html')<span class="adm-type">Formatted</span>@endif</label>
            @switch($def['type'])
                @case('textarea')
                    @include('admin.content._fmt')
                    <textarea id="{{ $fid }}" class="form-control" rows="3" name="{{ $name }}">{{ \App\Support\Html::forInput(is_string($val) ? $val : '') }}</textarea>
                    @break
                @case('html')
                    @include('admin.content._fmt')
                    <textarea id="{{ $fid }}" class="form-control" rows="2" name="{{ $name }}">{{ \App\Support\Html::forInput(is_string($val) ? $val : '') }}</textarea>
                    @break
                @case('lines')
                    <textarea id="{{ $fid }}" class="form-control" rows="{{ max(3, min(8, count((array) $val) + 1)) }}" name="{{ $name }}">{{ implode("\n", array_map([\App\Support\Html::class, 'forInput'], array_map('strval', (array) $val))) }}</textarea>
                    @break
                @case('repeater')
                    <div class="border rounded-3 p-2 bg-light" data-repeater>
                        <div data-rows>
                            @foreach(((array) $val) as $m => $sub)
                                @php $subRow = !empty($def['wrap']) ? ($sub[0] ?? []) : $sub; @endphp
                                @include('admin.content._subrow', ['defs' => $def['fields'], 'prefix' => $name . '[' . $m . ']', 'item' => $subRow])
                            @endforeach
                        </div>
                        <template data-sub-template>
                            @include('admin.content._subrow', ['defs' => $def['fields'], 'prefix' => $name . '[__M__]', 'item' => []])
                        </template>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" data-act="add-sub"><i class="bi bi-plus-lg me-1"></i> Add row</button>
                    </div>
                    @break
                @default
                    <input id="{{ $fid }}" type="text" class="form-control" name="{{ $name }}" value="{{ \App\Support\Html::forInput(is_string($val) ? $val : (is_scalar($val) ? (string) $val : '')) }}" maxlength="500" @if($isTitle) data-title-source @endif @if($def['type'] === 'url') placeholder="https://… or #" @endif>
            @endswitch
            @if(!empty($def['help']))<div class="form-text">{{ $def['help'] }}</div>@endif
        @endif
    </div>
@endforeach
