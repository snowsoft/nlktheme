<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('page_key');          // e.g. 'home', 'collection', 'product'
            $table->string('template')->default('index');
            $table->json('sections_order')->nullable();   // ordered list of section UUIDs
            $table->json('settings')->nullable();         // global page settings
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'page_key']);
            $table->index(['tenant_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_page_settings');
    }
};
