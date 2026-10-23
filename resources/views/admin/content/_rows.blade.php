{{-- The editable fields of one row. $defs = field definitions, $prefix = form-name prefix, $item = current values --}}
@foreach($defs as $key => $def)
    @php
        $name = $prefix . '[' . $key . ']';
        // PHP drops a field called "a[b]__file" (text after the last bracket), so the suffix goes inside the brackets
        $fileName = $prefix . '[' . $key . '__file]';
        $removeName = $prefix . '[' . $key . '__remove]';
        $val = is_array($item) ? ($item[$key] ?? null) : null;
        $fid = 'r_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
        $isTitle = isset($titleKey) && (string) $titleKey === (string) $key;
    @endphp
    <div class="mb-3">
        @if($def['type'] === 'image')
            <x-admin.image :name="$fileName" :remove-name="$removeName" :current="is_string($val) ? $val : null" :label="$def['label']" :compact="true" />
        @elseif($def['type'] === 'video')
            @php $isUpload = \App\Support\Media::isUpload(is_string($val) ? $val : null); @endphp
            <label class="form-label" for="{{ $fid }}">{{ $def['label'] }}<span class="adm-type">Video</span></label>
            <input id="{{ $fid }}" type="text" class="form-control" name="{{ $name }}" value="{{ $isUpload ? '' : \App\Support\Html::forInput(is_string($val) ? $val : '') }}" maxlength="500" placeholder="YouTube / Vimeo link, or https://… /video.mp4">
            <div class="form-text mb-2">Paste a YouTube or Vimeo link here, <strong>or</strong> upload a video file below.</div>
            <input type="file" class="form-control form-control-sm" name="{{ $fileName }}" accept="video/*,.mp4,.m4v,.mov,.webm,.ogv">
            <div class="form-text">MP4, WebM, MOV or OGG, up to {{ \App\Support\Media::VIDEO_MAX_KB / 1024 }} MB. A new upload replaces the current file; a link replaces an uploaded file.</div>
            @if($isUpload)
                <div class="small mt-2"><i class="bi bi-film me-1"></i>Current file: <a href="{{ asset($val) }}" target="_blank" rel="noopener">{{ basename($val) }}</a></div>
                <div class="form-check mt-1 small">
                    <input class="form-check-input" type="checkbox" name="{{ $removeName }}" value="1" id="rm_{{ $fid }}">
                    <label class="form-check-label" for="rm_{{ $fid }}">Remove this video file</label>
                </div>
            @endif
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
