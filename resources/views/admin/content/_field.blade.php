@php
    $id = $f->id;
    $err = $errors->first("fields.$id") ?: $errors->first("images.$id");
    $val = old("fields.$id", \App\Support\Html::forInput($f->value));
    $extra = str_contains($f->key, '~');                              // a paragraph added in the admin: can really be deleted
    $canAdd = !$extra && in_array($f->type, ['text', 'textarea', 'html'], true);
    $hidden = (bool) $f->hidden;
    $tools = $f->type !== 'toggle';                                   // a Show / hide field already is the switch
@endphp
<div class="adm-fieldrow adm-field {{ $extra ? 'is-extra' : '' }} {{ $hidden ? 'is-off' : '' }}" data-field>
    @if($tools)
    <div class="adm-fieldtools">
        @if($extra)
            <input type="hidden" name="delete_fields[{{ $id }}]" value="0" data-fld-delete>
            <button type="button" class="adm-fieldtool text-danger" data-fld-act="delete" title="Delete this added paragraph"><i class="bi bi-trash3"></i> Delete</button>
            <button type="button" class="adm-fieldtool d-none" data-fld-act="undo" title="Keep it"><i class="bi bi-arrow-counterclockwise"></i> Undo delete</button>
        @else
            <input type="hidden" name="hidden[{{ $id }}]" value="{{ $hidden ? 1 : 0 }}" data-fld-hidden>
            <button type="button" class="adm-fieldtool" data-fld-act="hide" title="Hide this on the website (the text is kept)"><i class="bi {{ $hidden ? 'bi-eye' : 'bi-eye-slash' }}"></i> <span>{{ $hidden ? 'Show' : 'Hide' }}</span></button>
        @endif
    </div>
    @endif
    <div class="adm-field__flag"><span class="adm-field__off">Hidden on the website</span><span class="adm-field__del d-none">Will be deleted when you save</span></div>
    @if($f->type === 'image')
        <x-admin.image :name="'images['.$id.']'" :remove-name="'remove_images['.$id.']'" :current="$f->value" :label="$f->label" help="PNG, JPG, WebP or GIF up to 5 MB. Uploading replaces the current picture." />
    @else
        <label class="form-label" for="fld_{{ $id }}">{{ $f->label }}<span class="adm-type">{{ \App\Models\ContentField::TYPES[$f->type] ?? $f->type }}</span></label>
        @if($f->type === 'text')
            <input id="fld_{{ $id }}" type="text" name="fields[{{ $id }}]" value="{{ $val }}" maxlength="500" class="form-control @if($err) is-invalid @endif">
        @elseif($f->type === 'toggle')
            <select id="fld_{{ $id }}" name="fields[{{ $id }}]" class="form-select" style="max-width: 260px">
                <option value="1" @selected($val !== '0')>Show</option>
                <option value="0" @selected($val === '0')>Hide</option>
            </select>
        @elseif($f->type === 'url')
            <input id="fld_{{ $id }}" type="text" name="fields[{{ $id }}]" value="{{ $val }}" maxlength="500" placeholder="https://… or contact-us.php or #" class="form-control @if($err) is-invalid @endif">
        @else
            @include('admin.content._fmt')
            <textarea id="fld_{{ $id }}" name="fields[{{ $id }}]" rows="{{ $f->type === 'html' ? 3 : 4 }}" class="form-control @if($err) is-invalid @endif">{{ $val }}</textarea>
            @if($f->type === 'html')<div class="form-text">Use the buttons above, or type tags: <code>&lt;strong&gt;</code> bold, <code>&lt;em&gt;</code> italic, <code>&lt;a href="…"&gt;</code> link, <code>&lt;ul&gt;&lt;li&gt;</code> list, <code>&lt;br&gt;</code> line break.</div>@endif
        @endif
        @if($err)<div class="invalid-feedback d-block">{{ $err }}</div>@endif
    @endif
    @if($canAdd)
        <div class="adm-fieldadd" data-fld-add-box data-base="{{ $id }}" data-kind="{{ $f->type === 'text' ? 'text' : 'area' }}">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-fld-act="add"><i class="bi bi-plus-lg me-1"></i> {{ $f->type === 'text' ? 'Add another line' : 'Add paragraph' }}</button>
        </div>
    @endif
</div>
