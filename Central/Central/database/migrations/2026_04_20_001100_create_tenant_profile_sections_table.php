<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_profile_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('section_key', 100);
            $table->string('label', 150);
            $table->boolean('enabled')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['school_id', 'section_key'], 'tps_school_section_uniq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_profile_sections');
    }
};
