<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if documents table exists (it does from previous migrations), then modify it
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'requirement_id')) {
                $table->foreignId('requirement_id')->nullable()->after('student_id')->constrained('document_requirements')->onDelete('cascade');
            }
            if (!Schema::hasColumn('documents', 'uploaded_at')) {
                $table->timestamp('uploaded_at')->nullable()->after('reviewed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['requirement_id']);
            $table->dropColumn(['requirement_id', 'uploaded_at']);
        });
    }
};
