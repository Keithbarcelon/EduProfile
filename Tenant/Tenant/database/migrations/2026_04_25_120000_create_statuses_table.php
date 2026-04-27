<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('statuses')) {
            Schema::create('statuses', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        DB::table('statuses')->insertOrIgnore([
            ['name' => 'regular', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'affirmative', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'probation', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
