<div class="adm-item">
    <div class="d-flex align-items-center gap-2 p-2">
        <input type="text" class="form-control" name="{{ $name }}" value="{{ \App\Support\Html::forInput($value) }}" maxlength="300">
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
