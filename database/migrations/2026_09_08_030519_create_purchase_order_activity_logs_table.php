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
        Schema::create('purchase_order_activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->noActionOnDelete();

            $table->string('action', 100);

            $table->text('description');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index([
                'purchase_order_id',
                'created_at',
            ]);

            $table->index([
                'user_id',
                'created_at',
            ]);

            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_activity_logs');
    }
};
