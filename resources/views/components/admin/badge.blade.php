@props(['status'])
@php
    [$cls, $text] = match ($status) {
        'published', true, 1 => ['ok', 'Published'],
        'draft' => ['draft', 'Draft'],
        'read' => ['off plain', 'Read'],
        'unread' => ['draft', 'New'],
        'active' => ['ok', 'Active'],
        'disabled', false, 0 => ['off', 'Disabled'],
        default => ['off', ucfirst((string) $status)],
    };
@endphp
<span class="adm-badge {{ $cls }}">{{ $slot->isEmpty() ? $text : $slot }}</span>
