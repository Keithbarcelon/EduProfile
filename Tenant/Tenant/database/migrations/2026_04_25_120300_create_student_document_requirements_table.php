<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_document_requirements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('document_name');
            $table->enum('required_for_status', ['regular', 'affirmative', 'probation']);
            $table->enum('state', ['required', 'archived', 'not_required'])->default('required');
            $table->timestamps();

            $table->unique(['student_id', 'document_name']);
            $table->index(['student_id', 'state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_document_requirements');
    }
};
