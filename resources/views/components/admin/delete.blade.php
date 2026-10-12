@props(['action', 'message' => 'This cannot be undone.', 'label' => 'Delete', 'icon' => 'bi-trash3', 'class' => 'btn btn-sm btn-outline-danger', 'title' => 'Delete this item?', 'confirmOk' => 'Yes, delete', 'iconOnly' => false])
<form method="post" action="{{ $action }}" class="d-inline" data-confirm="{{ $message }}" data-confirm-title="{{ $title }}" data-confirm-ok="{{ $confirmOk }}">
    @csrf @method('DELETE')
    <button type="submit" class="{{ $class }}" @if($iconOnly) title="{{ $label }}" aria-label="{{ $label }}" @endif><i class="bi {{ $icon }}"></i>@unless($iconOnly) {{ $label }}@endunless</button>
</form>
