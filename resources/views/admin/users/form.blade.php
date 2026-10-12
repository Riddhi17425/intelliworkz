@extends('admin.layouts.app')
@php $editing = $user->exists; @endphp
@section('title', $editing ? 'Edit user' : 'Add user')

@section('content')
<x-admin.head :title="$editing ? 'Edit user' : 'Add user'" :crumbs="['Users' => route('admin.users.index'), ($editing ? $user->name : 'New user') => null]"/>

<form method="post" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" data-once>
    @csrf @if($editing) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="adm-card"><div class="adm-card__head"><h2>Details</h2></div>
                <div class="adm-card__body">
                    <x-admin.field name="name" label="Full name" :value="$user->name" :required="true"/>
                    <x-admin.field name="email" type="email" label="E-mail address" :value="$user->email" :required="true"/>
                    <div class="row">
                        <x-admin.field class="col-md-6" name="password" type="password" :label="$editing ? 'New password' : 'Password'" :required="!$editing" :help="$editing ? 'Leave empty to keep the current password.' : 'At least 10 characters.'" autocomplete="new-password"/>
                        <x-admin.field class="col-md-6" name="password_confirmation" type="password" label="Repeat password" autocomplete="new-password"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="adm-card"><div class="adm-card__head"><h2>Access</h2></div>
                <div class="adm-card__body">
                    <div class="mb-3">
                        <label class="form-label" for="role">Role<span class="req">*</span></label>
                        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror">
                            @foreach(\App\Models\User::ROLES as $value => $label)
                                <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text"><strong>Editor</strong> edits page content and reads inquiries. <strong>Administrator</strong> can also delete, change company settings and manage users.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
                        <label class="form-check-label" for="is_active">Account is active</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="adm-formbar">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i> {{ $editing ? 'Save changes' : 'Create user' }}</button>
        <a class="btn btn-light" href="{{ route('admin.users.index') }}">Cancel</a>
    </div>
</form>
@endsection
