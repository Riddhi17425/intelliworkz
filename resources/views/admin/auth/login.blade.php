<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · {{ config('app.name') }} Admin</title>
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/vendor/icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/admin.css') }}?v={{ filemtime(public_path('admin-assets/css/admin.css')) }}">
</head>
<body>
<div class="adm-auth">
    <section class="adm-auth__art">
        <img src="{{ asset('assets/icons/logo.svg') }}" alt="Intelliworkz">
        <div>
            <h2>Manage your website, your way.</h2>
            <p>Update pages, services, banners and company details, and read every enquiry — without touching a line of code.</p>
        </div>
        <small class="text-white-50">© {{ date('Y') }} Intelliworkz Business Solutions Pvt. Ltd.</small>
    </section>
    <section class="adm-auth__form">
        <div class="adm-auth__box">
            <h1>Welcome back</h1>
            <p class="text-muted mb-4">Sign in to the admin panel.</p>

            @if(session('status'))
                <div class="alert alert-success py-2">{{ session('status') }}</div>
            @endif

            <form method="post" action="{{ route('admin.login.attempt') }}" novalidate data-once>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">E-mail address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="form-control @error('email') is-invalid @enderror" placeholder="you@company.com">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="form-control @error('password') is-invalid @enderror" placeholder="••••••••••">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                    <label class="form-check-label" for="remember">Keep me signed in on this device</label>
                </div>
                <button class="btn btn-primary w-100 py-2" type="submit">Sign in <i class="bi bi-arrow-right ms-1"></i></button>
            </form>
            <p class="small text-muted mt-4 mb-0">Trouble signing in? Ask an administrator to reset your password.</p>
        </div>
    </section>
</div>
</body>
</html>
