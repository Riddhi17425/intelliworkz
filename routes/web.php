<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel  (/admin/…)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware('admin.guard')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');

        Route::get('activity', [Admin\ActivityController::class, 'index'])->name('activity.index');

        // Pages: SEO / publishing, plus the section-by-section content editor
        Route::get('pages', [Admin\PageController::class, 'index'])->name('pages.index');
        Route::get('pages/create', [Admin\PageController::class, 'create'])->name('pages.create');
        Route::post('pages', [Admin\PageController::class, 'store'])->name('pages.store');
        Route::get('pages/{page}/edit', [Admin\PageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}', [Admin\PageController::class, 'update'])->name('pages.update');
        Route::patch('pages/{page}/status', [Admin\PageController::class, 'toggleStatus'])->name('pages.status');
        Route::delete('pages/{page}', [Admin\PageController::class, 'destroy'])->middleware('can:delete-content')->name('pages.destroy');
        Route::get('pages/{page}/content', [Admin\ContentController::class, 'editPage'])->name('pages.content');
        Route::put('pages/{page}/content', [Admin\ContentController::class, 'updatePage'])->name('pages.content.update');

        // Header / footer / sections shared by every page
        Route::get('shared', [Admin\ContentController::class, 'editShared'])->name('shared.edit');
        Route::put('shared', [Admin\ContentController::class, 'updateShared'])->name('shared.update');

        // Services menu (header) and footer columns
        Route::get('menu', [Admin\MenuController::class, 'index'])->name('menu.index');
        Route::post('menu/groups', [Admin\MenuController::class, 'storeGroup'])->name('menu.groups.store');
        Route::put('menu/groups/{group}', [Admin\MenuController::class, 'updateGroup'])->name('menu.groups.update');
        Route::post('menu/groups/{group}/move', [Admin\MenuController::class, 'moveGroup'])->name('menu.groups.move');
        Route::delete('menu/groups/{group}', [Admin\MenuController::class, 'destroyGroup'])->middleware('can:delete-content')->name('menu.groups.destroy');
        Route::post('menu/groups/{group}/items', [Admin\MenuController::class, 'storeItem'])->name('menu.items.store');
        Route::put('menu/items/{item}', [Admin\MenuController::class, 'updateItem'])->name('menu.items.update');
        Route::post('menu/items/{item}/move', [Admin\MenuController::class, 'moveItem'])->name('menu.items.move');
        Route::delete('menu/items/{item}', [Admin\MenuController::class, 'destroyItem'])->middleware('can:delete-content')->name('menu.items.destroy');

        // Contact inquiries
        Route::get('inquiries', [Admin\InquiryController::class, 'index'])->name('inquiries.index');
        Route::get('inquiries/export', [Admin\InquiryController::class, 'export'])->name('inquiries.export');
        Route::post('inquiries/bulk', [Admin\InquiryController::class, 'bulk'])->name('inquiries.bulk');
        Route::get('inquiries/{inquiry}', [Admin\InquiryController::class, 'show'])->name('inquiries.show');
        Route::patch('inquiries/{inquiry}', [Admin\InquiryController::class, 'toggleRead'])->name('inquiries.toggle');
        Route::delete('inquiries/{inquiry}', [Admin\InquiryController::class, 'destroy'])->middleware('can:delete-content')->name('inquiries.destroy');

        // Administrator-only
        Route::middleware('can:manage-settings')->group(function () {
            Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        });
        Route::middleware('can:manage-users')->group(function () {
            Route::resource('users', Admin\UserController::class)->except('show');
        });
    });
});

// Public website routes are registered below (see routes/site.php, loaded last so /admin is never swallowed).
require __DIR__ . '/site.php';
