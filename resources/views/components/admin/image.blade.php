@props(['name', 'label', 'current' => null, 'help' => null, 'removable' => true, 'compact' => false, 'accept' => 'image/png,image/jpeg,image/webp,image/gif', 'removeName' => null])
@php
    $id = 'img_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
    $errKey = preg_replace('/\[(\w+)\]/', '.$1', $name);
    $removeName = $removeName ?: 'remove_' . $name;
    $url = \App\Support\Media::url($current);
@endphp
<div {{ $attributes->only('class')->merge(['class' => 'mb-3']) }}>
    @if($label)<label class="form-label" for="{{ $id }}">{{ $label }}</label>@endif
    <div class="adm-img js-image {{ $compact ? 'is-compact' : '' }}">
        <div class="adm-img__preview">@if($url)<img src="{{ $url }}" alt="">@else<i class="bi bi-image"></i>@endif</div>
        <div class="adm-img__body">
            <span class="adm-img__name">{{ $current ? basename($current) : 'No image selected' }}</span>
            <input id="{{ $id }}" class="form-control form-control-sm @error($errKey) is-invalid @enderror" type="file" name="{{ $name }}" accept="{{ $accept }}">
            @if($removable && $current)
                <div class="form-check mt-2 small">
                    <input class="form-check-input" type="checkbox" name="{{ $removeName }}" value="1" id="rm_{{ $id }}" data-remove>
                    <label class="form-check-label" for="rm_{{ $id }}">Remove this image</label>
                </div>
            @endif
        </div>
    </div>
    @error($errKey)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @if($help)<div class="form-text">{{ $help }}</div>@endif
</div>
