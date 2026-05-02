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
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_id');
            $table->string('batch_number', 100);
            $table->date('expiry_date')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('quantity_sold')->default(0);
            $table->integer('quantity_remaining')->default(0);
            $table->decimal('unit_cost', 10, 4)->default(0.0000);
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->date('received_date');
            $table->string('supplier_batch_ref', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['inventory_id', 'batch_number']);
            $table->index('inventory_id');
            $table->index('batch_number');
            $table->index('expiry_date');

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventory')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
