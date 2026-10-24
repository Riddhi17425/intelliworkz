{{-- One entry of a key => label list (filter tabs). The key is what gallery items use in "Filter categories". --}}
<div class="adm-item" data-item>
    <div class="d-flex align-items-center gap-2 p-2">
        <span class="input-group-text small text-muted" title="Key used in “Filter categories”">{{ $key === 'new' ? 'new' : $key }}</span>
        <input type="text" class="form-control" name="{{ $name }}" value="{{ \App\Support\Html::forInput($value) }}" maxlength="200" @if($key === 'new') placeholder="Tab name (its key is made from the name when you save)" @endif>
        <div class="adm-item__tools">
            @if($schema->canReorder())
                <button type="button" class="btn btn-sm btn-light" data-act="up" title="Move up"><i class="bi bi-arrow-up"></i></button>
                <button type="button" class="btn btn-sm btn-light" data-act="down" title="Move down"><i class="bi bi-arrow-down"></i></button>
            @endif
            @if($schema->canRemove())
                <button type="button" class="btn btn-sm btn-light text-danger" data-act="remove" title="Remove"><i class="bi bi-x-lg"></i></button>
            @endif
        </div>
    </div>
</div>
