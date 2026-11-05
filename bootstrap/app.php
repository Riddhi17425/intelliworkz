<?php

use App\Http\Middleware\AdminGuard;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['admin.guard' => AdminGuard::class]);
        $middleware->web(prepend: [\App\Http\Middleware\CanonicalUrl::class]);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // unknown public address -> friendly page in the site design (the admin and JSON requests keep Laravel's own answer)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('admin', 'admin/*', 'up')) {
                return null;
            }
            // /About-Us -> /about-us when such a page exists
            if ($redirect = app(\App\Http\Controllers\PageController::class)->lowercaseRedirect($request)) {
                return $redirect;
            }
            return app(\App\Http\Controllers\PageController::class)->notFound($request);
        });
    })->create();
