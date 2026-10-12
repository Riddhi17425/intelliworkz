<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Page content is stored in two shapes:
 *  - content_fields: single values written in the template (headings, paragraphs, button labels, images)
 *  - content_lists:  repeatable items (FAQs, cards, workflow steps, metrics…), kept as JSON so the
 *                    original array shape — and therefore the template markup — stays unchanged.
 * "scope" is the page slug, or "global" for the header/footer/shared sections.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_fields', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 120);
            $table->string('section', 120);
            $table->string('key', 160);
            $table->string('label');
            $table->string('type', 20)->default('text');   // text | textarea | html | url | image
            $table->mediumText('value')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['scope', 'key']);
            $table->index(['scope', 'section', 'sort']);
        });

        Schema::create('content_lists', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 120);
            $table->string('key', 120);                     // variable name the template reads
            $table->string('section', 120)->nullable();
            $table->string('label');
            $table->json('items');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['scope', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_lists');
        Schema::dropIfExists('content_fields');
    }
};
