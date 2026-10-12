@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')
<x-admin.head title="Welcome back, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}" subtitle="Here is what is happening on your website today.">
    <x-slot:actions>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-primary"><i class="bi bi-file-earmark-richtext me-1"></i> Edit pages</a>
        <a href="{{ route('admin.inquiries.index') }}" class="btn btn-outline-secondary"><i class="bi bi-envelope-paper me-1"></i> View inquiries</a>
    </x-slot:actions>
</x-admin.head>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <a href="{{ route('admin.pages.index') }}" class="adm-card adm-stat">
            <span class="adm-stat__icon tone-navy"><i class="bi bi-file-earmark-text"></i></span>
            <div><div class="adm-stat__value">{{ $pages->total }}</div><div class="adm-stat__label">Pages</div>
                <div class="adm-stat__sub">{{ $pages->published }} published · {{ $pages->draft }} draft</div></div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('admin.menu.index') }}" class="adm-card adm-stat">
            <span class="adm-stat__icon tone-blue"><i class="bi bi-diagram-3"></i></span>
            <div><div class="adm-stat__value">{{ $services }}</div><div class="adm-stat__label">Services in menu</div>
                <div class="adm-stat__sub">{{ $servicesLive }} visible · {{ $services - $servicesLive }} hidden</div></div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('admin.inquiries.index') }}" class="adm-card adm-stat">
            <span class="adm-stat__icon tone-red"><i class="bi bi-envelope-paper"></i></span>
            <div><div class="adm-stat__value">{{ $inquiryTotal }}</div><div class="adm-stat__label">Contact inquiries</div>
                <div class="adm-stat__sub">{{ $inquiryUnread }} unread · {{ $inquiryWeek }} this week</div></div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <div class="adm-card adm-stat">
            <span class="adm-stat__icon tone-green"><i class="bi bi-pencil-square"></i></span>
            <div><div class="adm-stat__value">{{ $fieldCount + $listCount }}</div><div class="adm-stat__label">Editable content blocks</div>
                <div class="adm-stat__sub">{{ $fieldCount }} texts &amp; images · {{ $listCount }} lists</div></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="adm-card h-100">
            <div class="adm-card__head"><h2>Inquiries — last 14 days</h2><span class="ms-auto text-muted">{{ $trend->sum('count') }} received</span></div>
            <div class="adm-card__body">
                <div class="adm-bars" role="img" aria-label="Inquiries per day">
                    @foreach($trend as $d)
                        <div data-v="{{ $d['count'] }}" style="height: {{ max(3, round($d['count'] / $trendMax * 100)) }}%" data-tip="{{ $d['label'] }} · {{ $d['count'] }}"></div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-between mt-2 small text-muted"><span>{{ $trend->first()['label'] }}</span><span>{{ $trend->last()['label'] }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="adm-card h-100">
            <div class="adm-card__head"><h2>Quick actions</h2></div>
            <div class="adm-card__body d-grid gap-2">
                <a class="btn btn-light text-start" href="{{ route('admin.pages.edit', 'index') }}"><i class="bi bi-house-door me-2 text-danger"></i> Edit the homepage</a>
                <a class="btn btn-light text-start" href="{{ route('admin.menu.index') }}"><i class="bi bi-diagram-3 me-2 text-danger"></i> Add or reorder services</a>
                <a class="btn btn-light text-start" href="{{ route('admin.shared.edit') }}"><i class="bi bi-layout-text-window-reverse me-2 text-danger"></i> Update header, footer &amp; offices</a>
                @can('manage-settings')
                    <a class="btn btn-light text-start" href="{{ route('admin.settings.edit') }}"><i class="bi bi-image me-2 text-danger"></i> Change logo &amp; company details</a>
                @endcan
                <a class="btn btn-light text-start" href="{{ route('admin.pages.create') }}"><i class="bi bi-plus-circle me-2 text-danger"></i> Create a new page</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="adm-card h-100">
            <div class="adm-card__head"><h2>Latest inquiries</h2><a class="ms-auto" href="{{ route('admin.inquiries.index') }}">View all</a></div>
            @if($recentInquiries->isEmpty())
                <x-admin.empty icon="bi-envelope" title="No inquiries yet" text="Messages sent from the website forms will appear here."/>
            @else
                <div class="table-responsive">
                    <table class="table adm-table table-hover">
                        <tbody>
                        @foreach($recentInquiries as $i)
                            <tr class="{{ $i->is_read ? '' : 'is-unread' }}">
                                <td>
                                    <a class="row-title" href="{{ route('admin.inquiries.show', $i) }}">@unless($i->is_read)<span class="adm-dot"></span>@endunless{{ $i->name }}</a>
                                    <div class="small text-muted text-truncate" style="max-width: 320px">{{ $i->subject ?: \Illuminate\Support\Str::limit($i->message, 60) }}</div>
                                </td>
                                <td class="small text-muted d-none d-md-table-cell">{{ $i->source ?: '—' }}</td>
                                <td class="small text-muted text-end text-nowrap">{{ $i->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        <div class="adm-card h-100">
            <div class="adm-card__head"><h2>Recent activity</h2><a class="ms-auto" href="{{ route('admin.activity.index') }}">View all</a></div>
            <div class="adm-card__body">
                @if($activity->isEmpty())
                    <p class="text-muted mb-0">No activity recorded yet.</p>
                @else
                    <ul class="adm-feed">
                        @foreach($activity as $log)
                            <li><i class="bi {{ $log->icon() }}"></i>
                                <div><div>{{ $log->description }}</div><small>{{ $log->user?->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}</small></div></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
