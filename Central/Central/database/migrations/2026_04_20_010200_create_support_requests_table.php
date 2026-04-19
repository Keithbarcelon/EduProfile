<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('schools')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('subject', 255);
            $table->text('message');
            $table->enum('status', ['open', 'in_progress', 'resolved'])->default('open');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_requests');
    }
};
