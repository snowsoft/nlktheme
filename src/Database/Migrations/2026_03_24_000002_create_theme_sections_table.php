<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_settings_id')
                  ->constrained('theme_page_settings')
                  ->cascadeOnDelete();
            $table->string('tenant_id')->index();
            $table->string('section_id');          // UUID matching sections_order
            $table->string('type');                // e.g. 'hero', 'featured-products'
            $table->json('settings')->nullable();  // section-level settings
            $table->json('block_order')->nullable(); // ordered block keys
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('disabled')->default(false);
            $table->timestamps();

            $table->unique(['page_settings_id', 'section_id']);
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_sections');
    }
};
