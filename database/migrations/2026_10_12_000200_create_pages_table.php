<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('template', 60)->default('generic');   // view used to render it (system pages have their own)
            $table->boolean('is_system')->default(false);          // the original 12 pages: slug/template locked, cannot be deleted
            $table->string('title');
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->string('og_image')->nullable();
            $table->string('featured_image')->nullable();
            $table->longText('content')->nullable();               // rich text, generic pages only
            $table->string('status', 20)->default('draft');        // draft | published
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
