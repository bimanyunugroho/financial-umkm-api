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
        Schema::create('ai_insight_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50); // insights | ask
            $table->string('prompt_hash', 32); // md5 for dedup
            $table->text('response_text');
            $table->unsignedInteger('tokens_used')->default(0);
            $table->string('model', 50)->default('gpt-4o-mini');
            $table->jsonb('context_summary')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'created_at']);
            $table->index('prompt_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_insight_logs');
    }
};
