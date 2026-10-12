@extends('admin.layouts.app')
@section('title', 'Activity log')

@section('content')
<x-admin.head title="Activity log" subtitle="Who changed what, and when."/>

<div class="adm-card">
    <form class="adm-filters" method="get">
        <div class="adm-search"><i class="bi bi-search"></i><input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search activity…"></div>
        <select class="form-select" name="action" onchange="this.form.submit()">
            <option value="">All actions</option>
            @foreach($actions as $a)<option value="{{ $a }}" @selected(request('action') === $a)>{{ ucfirst($a) }}</option>@endforeach
        </select>
        <button class="btn btn-sm btn-primary">Filter</button>
        @if(request()->hasAny(['q', 'action']))<a class="btn btn-sm btn-light" href="{{ route('admin.activity.index') }}">Reset</a>@endif
    </form>

    @if($logs->isEmpty())
        <x-admin.empty icon="bi-clock-history" title="No activity found"/>
    @else
        <div class="table-responsive">
            <table class="table adm-table table-hover">
                <thead><tr><th>When</th><th>User</th><th>Action</th><th>Details</th><th class="d-none d-lg-table-cell">IP</th></tr></thead>
                <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="text-nowrap small"><span title="{{ $log->created_at->format('d M Y H:i:s') }}">{{ $log->created_at->diffForHumans() }}</span></td>
                        <td>{{ $log->user?->name ?? 'System' }}</td>
                        <td><span class="adm-badge off plain"><i class="bi {{ $log->icon() }} me-1"></i>{{ ucfirst($log->action) }}</span></td>
                        <td>{{ $log->description }}</td>
                        <td class="small text-muted d-none d-lg-table-cell">{{ $log->ip_address }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="adm-card__foot">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}{{ $logs->links() }}</div>
    @endif
</div>
@endsection
