@props(['title', 'subtitle' => null, 'crumbs' => []])
<div class="adm-head">
    <div>
        @if($crumbs)
            <p class="adm-crumbs">
                @foreach($crumbs as $label => $url)
                    @if($url)<a href="{{ $url }}">{{ $label }}</a> <span class="mx-1">/</span>@else<span>{{ $label }}</span>@endif
                @endforeach
            </p>
        @endif
        <h1>{{ $title }}</h1>
        @if($subtitle)<p>{{ $subtitle }}</p>@endif
    </div>
    @if(isset($actions) && !$actions->isEmpty())
        <div class="adm-head__actions">{{ $actions }}</div>
    @endif
</div>
