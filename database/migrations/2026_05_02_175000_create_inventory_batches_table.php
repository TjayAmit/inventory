<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->string('batch_number', 100)->unique();
            $table->unsignedBigInteger('purchase_order_item_id')->nullable();
            $table->integer('quantity');
            $table->integer('quantity_remaining');
            $table->decimal('unit_cost', 10, 4);
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('received_date');
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->string('location', 100)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['inventory_id', 'received_date']);
            $table->index('expiry_date');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
