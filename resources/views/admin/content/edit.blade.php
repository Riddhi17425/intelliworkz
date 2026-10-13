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

    @if(!empty($toggles))
        <div class="adm-card mb-3" id="sectionSwitches">
            <div class="adm-card__head"><h2>Sections shown on this page</h2><span class="ms-auto small text-muted">Untick a section to hide it on the website. Its text is kept.</span></div>
            <div class="adm-card__body">
                <input type="hidden" name="has_section_switches" value="1">
                <div class="row g-2">
                    <div class="col-sm-6 col-lg-4"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" checked disabled id="sw_hero"><label class="form-check-label" for="sw_hero">Hero (always shown)</label></div></div>
                    @foreach($toggles as $key => $label)
                        <div class="col-sm-6 col-lg-4"><div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="show_sections[{{ $key }}]" value="1" id="sw_{{ $key }}" @checked(old('has_section_switches') ? old("show_sections.$key") : $page->showsSection($key))>
                            <label class="form-check-label" for="sw_{{ $key }}">{{ $label }}</label>
                        </div></div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <nav class="adm-tabs" id="sectionTabs" aria-label="Sections">
        @foreach($sections as $s)<a href="#sec-{{ $s['id'] }}" data-spy="sec-{{ $s['id'] }}">{{ $s['name'] }}</a>@endforeach
    </nav>

    @error('content')<div class="alert alert-danger">{{ $message }}</div>@enderror

    @foreach($sections as $s)
        <section class="adm-card adm-fieldgroup mb-4" id="sec-{{ $s['id'] }}">
            <div class="adm-card__head">
                <h2>{{ $s['name'] }}</h2>
                <span class="ms-auto text-muted small">
                    @if($s['fields']->count()){{ $s['fields']->count() }} {{ \Illuminate\Support\Str::plural('field', $s['fields']->count()) }}@endif
                    @if($s['fields']->count() && count($s['lists'])) · @endif
                    @if(count($s['lists'])){{ count($s['lists']) }} {{ \Illuminate\Support\Str::plural('list', count($s['lists'])) }}@endif
                </span>
            </div>
            <div class="adm-card__body">
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
@endsection

@push('scripts')
    <script src="{{ asset('admin-assets/js/content-editor.js') }}?v={{ filemtime(public_path('admin-assets/js/content-editor.js')) }}"></script>
@endpush
