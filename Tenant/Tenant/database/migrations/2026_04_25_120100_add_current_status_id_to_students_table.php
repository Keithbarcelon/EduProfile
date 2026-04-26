<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (! Schema::hasColumn('students', 'current_status_id')) {
                $table->foreignId('current_status_id')->nullable()->after('status_category')->constrained('statuses')->nullOnDelete();
            }
        });

        $statusMap = DB::table('statuses')->pluck('id', 'name');

        if ($statusMap->isEmpty()) {
            return;
        }

        foreach (['regular', 'affirmative', 'probation'] as $statusName) {
            $statusId = (int) ($statusMap[$statusName] ?? 0);
            if ($statusId <= 0) {
                continue;
            }

            DB::table('students')
                ->where('status_category', $statusName)
                ->whereNull('current_status_id')
                ->update(['current_status_id' => $statusId]);
        }

        $regularId = (int) ($statusMap['regular'] ?? 0);
        if ($regularId > 0) {
            DB::table('students')
                ->whereNull('current_status_id')
                ->update(['current_status_id' => $regularId]);
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (Schema::hasColumn('students', 'current_status_id')) {
                $table->dropConstrainedForeignId('current_status_id');
            }
        });
    }
};
