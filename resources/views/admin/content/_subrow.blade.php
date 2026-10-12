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
