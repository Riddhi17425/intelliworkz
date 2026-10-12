@extends('admin.layouts.app')
@section('title', 'Inquiry from ' . $inquiry->name)

@section('content')
<x-admin.head :title="'Inquiry from ' . $inquiry->name" :crumbs="['Contact inquiries' => route('admin.inquiries.index'), $inquiry->name => null]">
    <x-slot:actions>
        @if($previous)<a class="btn btn-light" href="{{ route('admin.inquiries.show', $previous) }}" title="Newer"><i class="bi bi-chevron-left"></i></a>@endif
        @if($next)<a class="btn btn-light" href="{{ route('admin.inquiries.show', $next) }}" title="Older"><i class="bi bi-chevron-right"></i></a>@endif
        <a class="btn btn-primary" href="mailto:{{ $inquiry->email }}?subject={{ rawurlencode('Re: ' . ($inquiry->subject ?: 'Your enquiry to Intelliworkz')) }}"><i class="bi bi-reply me-1"></i> Reply by e-mail</a>
        <form method="post" action="{{ route('admin.inquiries.toggle', $inquiry) }}" class="d-inline">@csrf @method('PATCH')
            <button class="btn btn-outline-secondary"><i class="bi {{ $inquiry->is_read ? 'bi-envelope' : 'bi-envelope-open' }} me-1"></i> Mark as {{ $inquiry->is_read ? 'unread' : 'read' }}</button>
        </form>
        @can('delete-content')
            <x-admin.delete :action="route('admin.inquiries.destroy', $inquiry)" label="Delete" title="Delete this inquiry?" message="The message from {{ $inquiry->name }} will be permanently removed." />
        @endcan
    </x-slot:actions>
</x-admin.head>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="adm-card">
            <div class="adm-card__head"><h2>{{ $inquiry->subject ?: 'No subject' }}</h2><x-admin.badge :status="$inquiry->is_read ? 'read' : 'unread'"/></div>
            <div class="adm-card__body">
                @if($inquiry->message)
                    <div style="white-space: pre-wrap; line-height: 1.7">{{ $inquiry->message }}</div>
                @else
                    <p class="text-muted mb-0">The visitor did not write a message.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="adm-card">
            <div class="adm-card__head"><h2>Sender</h2></div>
            <div class="adm-card__body">
                <dl class="mb-0">
                    <dt class="small text-muted">Name</dt><dd class="fw-semibold">{{ $inquiry->name }}</dd>
                    <dt class="small text-muted">E-mail</dt><dd><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></dd>
                    <dt class="small text-muted">Phone</dt><dd>@if($inquiry->phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $inquiry->phone) }}">{{ $inquiry->phone }}</a>@else — @endif</dd>
                    <dt class="small text-muted">Company</dt><dd>{{ $inquiry->company ?: '—' }}</dd>
                    <dt class="small text-muted">Sent from page</dt><dd>{{ $inquiry->source ?: '—' }}</dd>
                    <dt class="small text-muted">Received</dt><dd class="mb-0">{{ $inquiry->created_at->format('d M Y, H:i') }} <span class="text-muted">({{ $inquiry->created_at->diffForHumans() }})</span></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
