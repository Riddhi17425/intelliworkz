<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }} Admin</title>
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/vendor/icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/admin.css') }}?v={{ filemtime(public_path('admin-assets/css/admin.css')) }}">
    @stack('head')
</head>
<body>
@php
    $nav = fn (string $pattern) => request()->routeIs($pattern) ? 'active' : '';
@endphp
<div class="adm-shell">
    <aside class="adm-side" aria-label="Main navigation">
        <a class="adm-side__brand" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('assets/icons/logo.svg') }}" alt="Intelliworkz">
            <span>Admin</span>
        </a>
        <nav class="adm-side__scroll">
            <div class="adm-side__label">Overview</div>
            <a class="adm-nav {{ $nav('admin.dashboard') }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>

            <div class="adm-side__label">Website content</div>
            <a class="adm-nav {{ $nav('admin.pages.*') }}" href="{{ route('admin.pages.index') }}"><i class="bi bi-file-earmark-richtext"></i> Pages</a>
            <a class="adm-nav {{ $nav('admin.menu.*') }}" href="{{ route('admin.menu.index') }}"><i class="bi bi-diagram-3"></i> Services &amp; menus</a>
            <a class="adm-nav {{ $nav('admin.shared.*') }}" href="{{ route('admin.shared.edit') }}"><i class="bi bi-layout-text-window-reverse"></i> Header, footer &amp; shared</a>

            <div class="adm-side__label">Enquiries</div>
            <a class="adm-nav {{ $nav('admin.inquiries.*') }}" href="{{ route('admin.inquiries.index') }}">
                <i class="bi bi-envelope-paper"></i> Contact inquiries
                @if($unreadInquiries > 0)<span class="badge rounded-pill text-bg-danger">{{ $unreadInquiries }}</span>@endif
            </a>

            <div class="adm-side__label">Configuration</div>
            @can('manage-settings')
                <a class="adm-nav {{ $nav('admin.settings.*') }}" href="{{ route('admin.settings.edit') }}"><i class="bi bi-sliders"></i> Company settings</a>
            @endcan
            @can('manage-users')
                <a class="adm-nav {{ $nav('admin.users.*') }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> Users</a>
            @endcan
            <a class="adm-nav {{ $nav('admin.activity.*') }}" href="{{ route('admin.activity.index') }}"><i class="bi bi-clock-history"></i> Activity log</a>
        </nav>
        <div class="adm-side__foot">
            <a href="{{ url('/') }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1"></i> View website</a>
        </div>
    </aside>
    <div class="adm-backdrop"></div>

    <div class="adm-main">
        <header class="adm-top">
            <button type="button" class="btn btn-light adm-top__toggle" data-nav-toggle aria-label="Open menu"><i class="bi bi-list fs-5"></i></button>
            <h2 class="adm-top__title">@yield('title', 'Dashboard')</h2>
            <div class="adm-top__right">
                <a class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center gap-1" href="{{ url('/') }}" target="_blank" rel="noopener"><i class="bi bi-eye"></i> Visit site</a>
                <div class="dropdown">
                    <button class="adm-user" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="adm-avatar">{{ auth()->user()->initials() }}</span>
                        <div class="text-start"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->roleLabel() }}</small></div>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="min-width: 220px">
                        <li class="px-2 pb-2 small text-muted">{{ auth()->user()->email }}</li>
                        <li><a class="dropdown-item rounded" href="{{ route('admin.profile.edit') }}"><i class="bi bi-person-gear me-2"></i>My profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="{{ route('admin.logout') }}">@csrf
                                <button class="dropdown-item rounded text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="adm-content">
            @yield('content')
        </main>
        <footer class="adm-foot">© {{ date('Y') }} Intelliworkz · Content management</footer>
    </div>
</div>

{{-- notifications --}}
<div class="adm-toasts" aria-live="polite">
    @if(session('success'))
        <div class="adm-toast"><i class="bi bi-check-circle-fill"></i><div>{{ session('success') }}</div><button type="button" aria-label="Close"><i class="bi bi-x-lg"></i></button></div>
    @endif
    @if(session('error'))
        <div class="adm-toast error"><i class="bi bi-exclamation-octagon-fill"></i><div>{{ session('error') }}</div><button type="button" aria-label="Close"><i class="bi bi-x-lg"></i></button></div>
    @endif
    @if($errors->any() && !session('error'))
        <div class="adm-toast error"><i class="bi bi-exclamation-triangle-fill"></i>
            <div><strong>Please fix the highlighted fields.</strong><div class="small text-muted">{{ $errors->count() }} {{ \Illuminate\Support\Str::plural('problem', $errors->count()) }} found.</div></div>
            <button type="button" aria-label="Close"><i class="bi bi-x-lg"></i></button></div>
    @endif
</div>

{{-- confirmation dialog used by every <form data-confirm="…"> --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4 text-center">
                <div class="adm-stat__icon tone-red mx-auto mb-3"><i class="bi bi-exclamation-triangle"></i></div>
                <h5 class="fw-bold mb-2" data-confirm-title>Are you sure?</h5>
                <p class="text-muted mb-4" data-confirm-message></p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" data-confirm-ok>Yes, delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('admin-assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/quill/quill.js') }}"></script>
<script src="{{ asset('admin-assets/js/admin.js') }}?v={{ filemtime(public_path('admin-assets/js/admin.js')) }}"></script>
@stack('scripts')
</body>
</html>
