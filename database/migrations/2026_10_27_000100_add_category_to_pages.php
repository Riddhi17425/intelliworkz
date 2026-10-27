<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Blog posts are ordinary admin pages (template "blog-post"); the category is shown on the blog cards. */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('category', 80)->nullable()->after('meta_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
