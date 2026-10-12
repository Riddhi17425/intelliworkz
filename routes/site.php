<?php

use Illuminate\Support\Facades\Route;

// Public website routes — added in the "connect the frontend" phase.
Route::get('/', fn () => 'Intelliworkz CMS is installed. Admin: ' . url('/admin'));
