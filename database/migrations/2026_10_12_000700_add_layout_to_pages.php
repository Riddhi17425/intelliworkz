<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->json('layout')->nullable()->after('content');   // ordered sections of a designed page (null = the design's own order)
            $table->dropColumn('hidden_sections');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->json('hidden_sections')->nullable()->after('content');
            $table->dropColumn('layout');
        });
    }
};
