<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 40);                  // company | branding | contact | social | seo
            $table->string('key', 80)->unique();
            $table->string('label');
            $table->string('type', 20)->default('text');  // text | textarea | email | url | image | html
            $table->text('value')->nullable();
            $table->string('help')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('contact_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('company', 160)->nullable();
            $table->string('email', 190);
            $table->string('phone', 40)->nullable();
            $table->string('subject', 200)->nullable();
            $table->text('message')->nullable();
            $table->string('source', 120)->nullable();    // page the form was sent from
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_read', 'created_at']);
            $table->index('email');
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40);                 // created | updated | deleted | login …
            $table->string('subject_type', 120)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description', 500);
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('contact_inquiries');
        Schema::dropIfExists('site_settings');
    }
};
