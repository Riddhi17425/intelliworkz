@props(['icon' => 'bi-inbox', 'title' => 'Nothing here yet', 'text' => null])
<div class="adm-empty">
    <i class="bi {{ $icon }}"></i>
    <h6 class="fw-bold text-dark mb-1">{{ $title }}</h6>
    @if($text)<p class="mb-3">{{ $text }}</p>@endif
    {{ $slot }}
</div>
