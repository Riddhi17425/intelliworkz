@extends('admin.layouts.app')
@section('title', 'Users')

@section('content')
<x-admin.head title="Users" subtitle="People who can sign in to this admin panel.">
    <x-slot:actions><a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Add user</a></x-slot:actions>
</x-admin.head>

<div class="adm-card">
    <form class="adm-filters" method="get">
        <div class="adm-search"><i class="bi bi-search"></i><input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search name or e-mail…"></div>
        <button class="btn btn-sm btn-primary">Search</button>
        @if(request('q'))<a class="btn btn-sm btn-light" href="{{ route('admin.users.index') }}">Reset</a>@endif
    </form>
    <div class="table-responsive">
        <table class="table adm-table table-hover">
            <thead><tr><th>User</th><th>Role</th><th>Status</th><th class="d-none d-md-table-cell">Last sign-in</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($users as $u)
                <tr>
                    <td><div class="d-flex align-items-center gap-2"><span class="adm-avatar">{{ $u->initials() }}</span>
                        <div><a class="row-title" href="{{ route('admin.users.edit', $u) }}">{{ $u->name }}</a>@if($u->is(auth()->user())) <span class="badge text-bg-light">You</span>@endif
                            <div class="small text-muted">{{ $u->email }}</div></div></div></td>
                    <td>{{ $u->roleLabel() }}</td>
                    <td><x-admin.badge :status="$u->is_active ? 'active' : 'disabled'"/></td>
                    <td class="small text-muted d-none d-md-table-cell">{{ $u->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.users.edit', $u) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                        @unless($u->is(auth()->user()))
                            <x-admin.delete :action="route('admin.users.destroy', $u)" :icon-only="true" label="Delete user" title="Delete {{ $u->name }}?" message="This account will no longer be able to sign in." />
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty icon="bi-people" title="No users found"/></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="adm-card__foot">{{ $users->total() }} {{ \Illuminate\Support\Str::plural('user', $users->total()) }}{{ $users->links() }}</div>
</div>
@endsection
