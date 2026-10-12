<?php

namespace Database\Seeders;

use App\Support\ContentImporter;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Loads the website content. It never creates a user: use `php artisan admin:create` for that.
     * Safe to run repeatedly — existing (possibly edited) content is left alone.
     */
    public function run(): void
    {
        (new ContentImporter())->run();
    }
}
