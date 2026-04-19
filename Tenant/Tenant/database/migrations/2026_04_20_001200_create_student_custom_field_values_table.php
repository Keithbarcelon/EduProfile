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
        Schema::create('student_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('field_key', 120);
            $table->text('field_value')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'field_key'], 'scfv_student_field_uniq');
            $table->index(['school_id', 'field_key'], 'scfv_school_field_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_custom_field_values');
    }
};
