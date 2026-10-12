<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
| The original site linked to files such as seo-services.php, so those addresses keep working;
| the same page is also reachable without the extension. "/" is the home page.
*/
Route::get('/', [PageController::class, 'show'])->defaults('slug', 'index')->name('home');
Route::get('/index.php', [PageController::class, 'show'])->defaults('slug', 'index');
Route::get('/{slug}.php', [PageController::class, 'show'])->where('slug', '[a-z0-9][a-z0-9-]*');
Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '[a-z0-9][a-z0-9-]*')->name('page');
