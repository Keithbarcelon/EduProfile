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
        if (! Schema::hasColumn('students', 'school_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('school_id')->nullable()->after('id');
            });
        }

        if (! Schema::hasColumn('students', 'middle_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('middle_name')->nullable()->after('first_name');
            });
        }

        if (! Schema::hasColumn('students', 'suffix')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('suffix')->nullable()->after('last_name');
            });
        }

        if (! Schema::hasColumn('students', 'guardian_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('guardian_name')->nullable()->after('address');
            });
        }

        if (! Schema::hasColumn('students', 'guardian_contact')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('guardian_contact')->nullable()->after('guardian_name');
            });
        }

        if (! Schema::hasColumn('students', 'emergency_contact_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('emergency_contact_name')->nullable()->after('guardian_contact');
            });
        }

        if (! Schema::hasColumn('students', 'emergency_contact_number')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('emergency_contact_number')->nullable()->after('emergency_contact_name');
            });
        }

        Schema::table('students', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
        });

        if (Schema::hasColumn('students', 'middle_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('middle_name');
            });
        }

        if (Schema::hasColumn('students', 'suffix')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('suffix');
            });
        }

        if (Schema::hasColumn('students', 'guardian_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('guardian_name');
            });
        }

        if (Schema::hasColumn('students', 'guardian_contact')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('guardian_contact');
            });
        }

        if (Schema::hasColumn('students', 'emergency_contact_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('emergency_contact_name');
            });
        }

        if (Schema::hasColumn('students', 'emergency_contact_number')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('emergency_contact_number');
            });
        }

        if (Schema::hasColumn('students', 'school_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('school_id');
            });
        }
    }
};
