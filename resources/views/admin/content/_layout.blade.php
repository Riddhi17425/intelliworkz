{{-- "Sections on this page": show / hide, order, sections copied in from other designs. Posted with the content form. --}}
<div class="adm-card mb-3" id="layoutCard">
    <div class="adm-card__head">
        <h2>Sections on this page</h2>
        <span class="ms-auto small text-muted">Switch a section off to hide it on the website (its text is kept), move it up or down, or add a section from another page.</span>
    </div>
    <div class="adm-card__body">
        <input type="hidden" name="has_layout_form" value="1">
        <ul class="adm-layout" data-layout-list>
            <li class="adm-layout__row is-fixed">
                <span class="adm-layout__tools"><i class="bi bi-lock"></i></span>
                <span class="adm-layout__name">Hero <small class="text-muted">· always first</small></span>
            </li>
            @foreach($layout as $i => $row)
                <li class="adm-layout__row {{ $row['hidden'] ? 'is-off' : '' }}" data-layout-row>
                    <input type="hidden" name="layout[{{ $i }}][id]" value="{{ $row['id'] }}">
                    <span class="adm-layout__tools">
                        <button type="button" class="btn btn-sm btn-light" data-layout-move="-1" title="Move up"><i class="bi bi-arrow-up"></i></button>
                        <button type="button" class="btn btn-sm btn-light" data-layout-move="1" title="Move down"><i class="bi bi-arrow-down"></i></button>
                    </span>
                    <span class="adm-layout__name">
                        {{ $row['label'] }}
                        @if($row['design'])<span class="badge text-bg-light border ms-1">added from {{ $row['design'] }}</span>@endif
                    </span>
                    <span class="form-check form-switch m-0 ms-auto">
                        <input class="form-check-input" type="checkbox" role="switch" id="show_{{ $row['id'] }}" name="layout[{{ $i }}][show]" value="1" data-layout-show @checked(old('has_layout_form') ? old("layout.$i.show") : !$row['hidden'])>
                        <label class="form-check-label small" for="show_{{ $row['id'] }}">Show</label>
                    </span>
                    @if($row['removeUrl'])
                        <button type="button" class="btn btn-sm btn-light text-danger" data-remove-section="{{ $row['removeUrl'] }}" data-remove-name="{{ $row['label'] }}" title="Remove this added section"><i class="bi bi-trash3"></i></button>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="adm-layout__add">
            <strong class="small">Add a section from another page</strong>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <select class="form-select" name="tpl" form="addSectionForm" aria-label="Page design" style="max-width: 300px" required>
                    <option value="">Page design…</option>
                    @foreach($designs as $slug => $title)<option value="{{ $slug }}">{{ $title }}</option>@endforeach
                </select>
                <select class="form-select" name="key" form="addSectionForm" aria-label="Section" style="max-width: 320px" required>
                    <option value="">Section…</option>
                    @foreach($sectionLabels as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                </select>
                <button type="submit" class="btn btn-outline-primary" form="addSectionForm"><i class="bi bi-plus-lg me-1"></i> Add section</button>
            </div>
            <div class="form-text">The new section gets a copy of that page's text, pictures and lists, so you can change them for this page only. It keeps the look of the page it comes from.</div>
        </div>
    </div>
</div>
