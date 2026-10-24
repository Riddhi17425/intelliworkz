@extends('admin.layouts.app')
@section('title', 'Company settings')

@php
    $icons = ['company' => 'bi-building', 'branding' => 'bi-palette', 'contact' => 'bi-telephone', 'social' => 'bi-share', 'seo' => 'bi-search'];
    $active = request('tab', old('tab', 'company'));
    if (!isset($groups[$active])) { $active = 'company'; }
    // Re-open the tab that holds a validation error.
    foreach ($settings as $g => $rows) {
        foreach ($rows as $r) { if ($errors->has('s.' . $r->key)) { $active = $g; break 2; } }
    }
@endphp

@section('content')
<x-admin.head title="Company settings" subtitle="Logo, contact details, social links and default SEO. Changes appear on the website straight away."/>

<form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" data-once>
    @csrf @method('PUT')
    <input type="hidden" name="tab" value="{{ $active }}" id="activeTab">

    <ul class="nav adm-tabs mb-3" role="tablist">
        @foreach($groups as $g => $label)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $g === $active ? 'active' : '' }}" type="button" role="tab" data-bs-toggle="tab" data-bs-target="#tab-{{ $g }}" data-tab="{{ $g }}">
                    <i class="bi {{ $icons[$g] ?? 'bi-gear' }} me-1"></i>{{ $label }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @foreach($groups as $g => $label)
            <div class="tab-pane fade {{ $g === $active ? 'show active' : '' }}" id="tab-{{ $g }}" role="tabpanel">
                <div class="adm-card">
                    <div class="adm-card__head"><h2>{{ $label }}</h2></div>
                    <div class="adm-card__body">
                        @forelse($settings[$g] ?? [] as $s)
                            @php $name = 's[' . $s->key . ']'; @endphp
                            @if($s->type === 'image')
                                <x-admin.image :name="$name" :label="$s->label" :current="$s->value" :help="$s->help" :remove-name="'remove_' . $s->key" :dark="$s->key === 'logo_header'"
                                    :accept="$s->key === 'favicon' ? 'image/png,image/jpeg,image/webp,image/gif' : 'image/svg+xml,image/png,image/jpeg,image/webp,image/gif'"/>
                            @else
                                <x-admin.field :name="$name" :label="$s->label" :type="$s->type === 'textarea' ? 'textarea' : ($s->type === 'email' ? 'email' : ($s->type === 'url' ? 'text' : 'text'))" :value="$s->value" :help="$s->help" :rows="4"/>
                            @endif
                        @empty
                            <p class="text-muted mb-0">Nothing to configure here.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="adm-formbar">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i> Save settings</button>
        <a class="btn btn-light" href="{{ route('admin.dashboard') }}">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-tab]').forEach(function (b) {
    b.addEventListener('shown.bs.tab', function () { document.getElementById('activeTab').value = b.dataset.tab; });
});
</script>
@endpush
