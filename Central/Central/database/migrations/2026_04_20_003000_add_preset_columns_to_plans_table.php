<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            if (! Schema::hasColumn('plans', 'is_system_preset')) {
                $table->boolean('is_system_preset')->default(false)->after('is_active');
            }

            if (! Schema::hasColumn('plans', 'preset_key')) {
                $table->string('preset_key')->nullable()->after('is_system_preset');
            }
        });

        $presetMap = [
            'basic' => [
                'name' => 'Basic',
                'description' => 'Starter package for small schools.',
                'billing_cycle' => 'monthly',
                'price' => 499.00,
            ],
            'standard' => [
                'name' => 'Standard',
                'description' => 'Balanced package for growing schools.',
                'billing_cycle' => 'monthly',
                'price' => 1299.00,
            ],
            'premium' => [
                'name' => 'Premium',
                'description' => 'Advanced package for large institutions.',
                'billing_cycle' => 'monthly',
                'price' => 2499.00,
            ],
        ];

        foreach ($presetMap as $slug => $preset) {
            $existing = DB::table('plans')->where('slug', $slug)->first();

            if ($existing) {
                DB::table('plans')
                    ->where('id', $existing->id)
                    ->update([
                        'is_system_preset' => true,
                        'preset_key' => $slug,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table('plans')->insert([
                'name' => $preset['name'],
                'slug' => $slug,
                'description' => $preset['description'],
                'billing_cycle' => $preset['billing_cycle'],
                'price' => $preset['price'],
                'is_sale' => false,
                'sale_price' => null,
                'starts_at' => null,
                'ends_at' => null,
                'is_active' => true,
                'is_system_preset' => true,
                'preset_key' => $slug,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            if (Schema::hasColumn('plans', 'preset_key')) {
                $table->dropColumn('preset_key');
            }

            if (Schema::hasColumn('plans', 'is_system_preset')) {
                $table->dropColumn('is_system_preset');
            }
        });
    }
};
