<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('document_requirement_id')->nullable()->after('student_id')->constrained('document_requirements')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['document_requirement_id']);
            $table->dropColumn('document_requirement_id');
        });
    }
};
