<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Any text or picture of a page can be switched off from the admin without losing what was written: the row stays,
 * the website just reads it as empty. Extra paragraphs added in the admin are stored as rows keyed "<field>~<n>".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_fields', function (Blueprint $table) {
            $table->boolean('hidden')->default(false)->after('sort');
        });
    }

    public function down(): void
    {
        Schema::table('content_fields', function (Blueprint $table) {
            $table->dropColumn('hidden');
        });
    }
};
