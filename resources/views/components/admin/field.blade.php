@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'help' => null, 'placeholder' => null, 'rows' => 4, 'id' => null, 'attrs' => []])
@php
    $id = $id ?: 'f_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
    $errKey = preg_replace('/\[(\w+)\]/', '.$1', $name);
    $current = old(preg_replace('/\[\]$/', '', $errKey), $value);
@endphp
<div {{ $attributes->only('class')->merge(['class' => 'mb-3']) }}>
    <label class="form-label" for="{{ $id }}">{{ $label }}@if($required)<span class="req" title="Required">*</span>@endif</label>
    @if($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}" @required($required)
                  {{ $attributes->except('class')->merge(['class' => 'form-control' . ($errors->has($errKey) ? ' is-invalid' : '')]) }} @foreach($attrs as $k => $v){{ $k }}="{{ $v }}" @endforeach>{{ $current }}</textarea>
    @elseif($type === 'rich')
        <textarea id="{{ $id }}" name="{{ $name }}" class="js-rich">{{ $current }}</textarea>
        @error($errKey)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @else
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}" value="{{ $type === 'password' ? '' : $current }}" placeholder="{{ $placeholder }}" @required($required)
               {{ $attributes->except('class')->merge(['class' => 'form-control' . ($errors->has($errKey) ? ' is-invalid' : '')]) }} @foreach($attrs as $k => $v){{ $k }}="{{ $v }}" @endforeach>
    @endif
    @if($type !== 'rich')@error($errKey)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror @endif
    @if($help)<div class="form-text">{{ $help }}</div>@endif
</div>
