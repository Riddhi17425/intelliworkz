@php
    $id = $f->id;
    $err = $errors->first("fields.$id") ?: $errors->first("images.$id");
    $val = old("fields.$id", \App\Support\Html::forInput($f->value));
@endphp
<div class="adm-fieldrow">
    @if($f->type === 'image')
        <x-admin.image :name="'images['.$id.']'" :remove-name="'remove_images['.$id.']'" :current="$f->value" :label="$f->label" help="PNG, JPG, WebP or GIF up to 5 MB. Uploading replaces the current picture." />
    @else
        <label class="form-label" for="fld_{{ $id }}">{{ $f->label }}<span class="adm-type">{{ \App\Models\ContentField::TYPES[$f->type] ?? $f->type }}</span></label>
        @if($f->type === 'text')
            <input id="fld_{{ $id }}" type="text" name="fields[{{ $id }}]" value="{{ $val }}" maxlength="500" class="form-control @if($err) is-invalid @endif">
        @elseif($f->type === 'url')
            <input id="fld_{{ $id }}" type="text" name="fields[{{ $id }}]" value="{{ $val }}" maxlength="500" placeholder="https://… or contact-us.php or #" class="form-control @if($err) is-invalid @endif">
        @else
            @include('admin.content._fmt')
            <textarea id="fld_{{ $id }}" name="fields[{{ $id }}]" rows="{{ $f->type === 'html' ? 3 : 4 }}" class="form-control @if($err) is-invalid @endif">{{ $val }}</textarea>
            @if($f->type === 'html')<div class="form-text">Use the buttons above, or type tags: <code>&lt;strong&gt;</code> bold, <code>&lt;em&gt;</code> italic, <code>&lt;a href="…"&gt;</code> link, <code>&lt;ul&gt;&lt;li&gt;</code> list, <code>&lt;br&gt;</code> line break.</div>@endif
        @endif
        @if($err)<div class="invalid-feedback d-block">{{ $err }}</div>@endif
    @endif
</div>
