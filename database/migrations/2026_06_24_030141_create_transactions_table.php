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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->decimal('amount', 15, 2);
            $table->string('description', 500);
            $table->date('date');
            $table->string('payment_method', 20); // cash | transfer | qris | ewallet | credit
            $table->text('notes')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'type', 'date']);
            $table->index(['user_id', 'category_id', 'date']);
            $table->index(['user_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
