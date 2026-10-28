@if(count($defs) >= 3)
    {{-- a row with several fields: one tidy card, every field on its own line, labelled --}}
    <div class="border rounded-3 bg-white p-3 mb-2 position-relative" data-sub>
        <button type="button" class="btn btn-sm btn-light text-danger position-absolute top-0 end-0 m-2" data-act="remove-sub" title="Remove row"><i class="bi bi-x-lg"></i></button>
        <div class="row g-2 pe-5">
            @foreach($defs as $key => $def)
                @php $v = is_array($item) ? ($item[$key] ?? '') : ''; @endphp
                <div class="col-12">
                    <label class="form-label small fw-semibold mb-1">{{ $def['label'] }}</label>
                    @if($def['type'] === 'video')
                        @php
                            $isUpload = \App\Support\Media::isUpload(is_string($v) ? $v : null);
                            $vid = 'sv_' . preg_replace('/[^a-z0-9_]/i', '_', $prefix . '_' . $key);
                        @endphp
                        {{-- PHP drops a field called "a[b]__file", so the suffix goes inside the brackets --}}
                        <input type="text" class="form-control form-control-sm" name="{{ $prefix }}[{{ $key }}]" value="{{ $isUpload ? '' : \App\Support\Html::forInput((string) $v) }}" maxlength="500" placeholder="YouTube / Vimeo link, or https://… /video.mp4">
                        <div class="form-text my-1">Paste a link <strong>or</strong> upload a video file:</div>
                        <input type="file" class="form-control form-control-sm" name="{{ $prefix }}[{{ $key }}__file]" accept="video/*,.mp4,.m4v,.mov,.webm,.ogv">
                        <div class="form-text">MP4, WebM, MOV or OGG, up to {{ \App\Support\Media::VIDEO_MAX_KB / 1024 }} MB. A new upload replaces the current file; a link replaces an uploaded file.</div>
                        @if($isUpload)
                            <input type="hidden" name="{{ $prefix }}[{{ $key }}__keep]" value="{{ $v }}">
                            <div class="small mt-1"><i class="bi bi-film me-1"></i>Current file: <a href="{{ asset($v) }}" target="_blank" rel="noopener">{{ basename($v) }}</a></div>
                            <div class="form-check small mt-1">
                                <input class="form-check-input" type="checkbox" name="{{ $prefix }}[{{ $key }}__remove]" value="1" id="rm_{{ $vid }}">
                                <label class="form-check-label" for="rm_{{ $vid }}">Remove this video file</label>
                            </div>
                        @endif
                    @elseif($def['type'] === 'textarea')
                        <textarea class="form-control form-control-sm" rows="2" name="{{ $prefix }}[{{ $key }}]">{{ \App\Support\Html::forInput((string) $v) }}</textarea>
                    @else
                        <input type="text" class="form-control form-control-sm" name="{{ $prefix }}[{{ $key }}]" value="{{ \App\Support\Html::forInput((string) $v) }}" maxlength="500">
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@else
<div class="d-flex gap-2 align-items-start mb-2" data-sub>
    <div class="row g-2 flex-grow-1">
        @foreach($defs as $key => $def)
            @php $v = is_array($item) ? ($item[$key] ?? '') : ''; @endphp
            <div class="{{ count($defs) > 1 && $def['type'] !== 'textarea' ? 'col-md-5' : 'col-md-7' }}">
                @if($def['type'] === 'textarea')
                    <textarea class="form-control form-control-sm" rows="2" name="{{ $prefix }}[{{ $key }}]" placeholder="{{ $def['label'] }}">{{ \App\Support\Html::forInput((string) $v) }}</textarea>
                @else
                    <input type="text" class="form-control form-control-sm" name="{{ $prefix }}[{{ $key }}]" value="{{ \App\Support\Html::forInput((string) $v) }}" placeholder="{{ $def['label'] }}" maxlength="500">
                @endif
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-light text-danger" data-act="remove-sub" title="Remove row"><i class="bi bi-x-lg"></i></button>
</div>
@endif
