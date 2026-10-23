@extends('admin.layouts.app')
@section('title', $title)

@section('content')
<x-admin.head :title="$title" :crumbs="$crumbs" :subtitle="$intro">
    <x-slot:actions>
        @if($seoUrl)<a href="{{ $seoUrl }}" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i> SEO &amp; publishing</a>@endif
        <a href="{{ $viewUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right me-1"></i> View live</a>
    </x-slot:actions>
</x-admin.head>

<form method="post" action="{{ $action }}" enctype="multipart/form-data" data-content-form data-dirty-guard data-once>
    @csrf @method('PUT')

    @if(!empty($layout))
        @include('admin.content._layout')
    @endif

    <nav class="adm-tabs" id="sectionTabs" aria-label="Sections">
        @foreach($sections as $s)<a href="#sec-{{ $s['id'] }}" data-spy="sec-{{ $s['id'] }}">{{ $s['name'] }}</a>@endforeach
    </nav>

    @error('content')<div class="alert alert-danger">{{ $message }}</div>@enderror

    @if(count($sections) > 1)
        <div class="adm-sections-bar">
            <span class="small text-muted">{{ count($sections) }} sections. Click a section to open it.</span>
            <div class="ms-auto d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-sections-all="open"><i class="bi bi-arrows-expand me-1"></i> Open all</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-sections-all="close"><i class="bi bi-arrows-collapse me-1"></i> Close all</button>
            </div>
        </div>
    @endif

    @foreach($sections as $s)
        <section class="adm-card adm-fieldgroup adm-section mb-3" id="sec-{{ $s['id'] }}" data-section>
            <div class="adm-card__head adm-section__head" data-section-toggle role="button" tabindex="0" aria-expanded="false" aria-controls="secbody-{{ $s['id'] }}">
                <i class="bi bi-chevron-right adm-section__chev"></i>
                <h2>{{ $s['name'] }}</h2>
                <span class="ms-auto text-muted small">
                    @if($s['fields']->count()){{ $s['fields']->count() }} {{ \Illuminate\Support\Str::plural('field', $s['fields']->count()) }}@endif
                    @if($s['fields']->count() && count($s['lists'])) · @endif
                    @if(count($s['lists'])){{ count($s['lists']) }} {{ \Illuminate\Support\Str::plural('list', count($s['lists'])) }}@endif
                </span>
            </div>
            <div class="adm-card__body" id="secbody-{{ $s['id'] }}">
                @foreach($s['fields'] as $f)
                    @include('admin.content._field', ['f' => $f])
                @endforeach
                @foreach($s['lists'] as $l)
                    @include('admin.content._list', ['list' => $l['model'], 'schema' => $l['schema']])
                @endforeach
            </div>
        </section>
    @endforeach

    <div class="adm-formbar">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i> Save changes</button>
        <a class="btn btn-light" href="{{ url()->current() }}">Discard</a>
        <span class="small text-muted ms-auto d-none d-md-inline">Changes go live as soon as you save.</span>
    </div>
</form>

@if(!empty($layout))
    {{-- outside the big form (forms cannot nest); the selects and the button above point here with form="addSectionForm" --}}
    <form id="addSectionForm" method="post" action="{{ route('admin.pages.sections.add', $page) }}">@csrf</form>
    <form id="removeSectionForm" method="post" action="" data-confirm="" data-confirm-title="Remove this section?" data-confirm-ok="Yes, remove">@csrf @method('DELETE')</form>
@endif
@endsection

@push('scripts')
    <script src="{{ asset('admin-assets/js/content-editor.js') }}?v={{ filemtime(public_path('admin-assets/js/content-editor.js')) }}"></script>
@endpush
