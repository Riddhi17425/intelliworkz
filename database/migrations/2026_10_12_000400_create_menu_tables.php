<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/* Header "Services" mega-menu (location = header) and footer link columns (location = footer). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_groups', function (Blueprint $table) {
            $table->id();
            $table->string('location', 20);              // header | footer
            $table->string('handle', 60)->nullable();    // stable id used by the footer layout (industries, products…)
            $table->string('title');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['location', 'sort']);
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_group_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('url', 500)->default('#');
            $table->foreignId('page_id')->nullable()->constrained()->nullOnDelete();   // optional link to a CMS page
            $table->string('description', 500)->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_published')->default(true);
            $table->boolean('new_tab')->default(false);
            $table->timestamps();

            $table->index(['menu_group_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menu_groups');
    }
};
