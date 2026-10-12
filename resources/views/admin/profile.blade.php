@extends('admin.layouts.app')
@section('title', 'My profile')

@section('content')
<x-admin.head title="My profile" subtitle="Your name, e-mail and password."/>

<form method="post" action="{{ route('admin.profile.update') }}" data-once>
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="adm-card"><div class="adm-card__head"><h2>Account</h2></div>
                <div class="adm-card__body">
                    <x-admin.field name="name" label="Full name" :value="$user->name" :required="true"/>
                    <x-admin.field name="email" type="email" label="E-mail address" :value="$user->email" :required="true" help="You sign in with this address."/>
                    <div class="small text-muted">Role: <strong>{{ $user->roleLabel() }}</strong>@if($user->last_login_at) · Last sign-in {{ $user->last_login_at->diffForHumans() }}@endif</div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="adm-card"><div class="adm-card__head"><h2>Change password</h2></div>
                <div class="adm-card__body">
                    <x-admin.field name="current_password" type="password" label="Current password" help="Only needed when setting a new password." autocomplete="current-password"/>
                    <x-admin.field name="password" type="password" label="New password" help="At least 10 characters." autocomplete="new-password"/>
                    <x-admin.field name="password_confirmation" type="password" label="Repeat new password" autocomplete="new-password"/>
                </div>
            </div>
        </div>
    </div>
    <div class="adm-formbar"><button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i> Save profile</button></div>
</form>
@endsection
