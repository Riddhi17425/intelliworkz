<?php

use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
| The original site linked to files such as seo-services.php, so those addresses keep working;
| the same page is also reachable without the extension. "/" is the home page.
*/

// enquiry / contact forms of every page (rate-limited, CSRF-protected, validated server-side)
Route::post('/inquiries', [InquiryController::class, 'store'])->middleware('throttle:inquiries')->name('inquiries.store');

Route::get('/thank-you', [PageController::class, 'thanks'])->name('thanks');
Route::get('/', [PageController::class, 'show'])->defaults('slug', 'index')->name('home');
Route::get('/index.php', [PageController::class, 'show'])->defaults('slug', 'index');
Route::get('/{slug}.php', [PageController::class, 'show'])->where('slug', '[a-z0-9][a-z0-9-]*');
Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '[a-z0-9][a-z0-9-]*')->name('page');
