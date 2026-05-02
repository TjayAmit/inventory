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
        Schema::create('sales_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('inventory_batch_id')->nullable();
            $table->string('product_name');
            $table->string('product_sku', 100);
            $table->integer('quantity')->default(0);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('unit_cost', 10, 4)->nullable(); // FIFO cost at time of sale
            $table->decimal('tax_rate', 5, 4)->default(0.0000);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('discount_percent', 5, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('line_total', 12, 2)->default(0.00);
            $table->decimal('line_cost', 12, 2)->nullable(); // Total cost for profit calculation
            $table->decimal('line_profit', 12, 2)->nullable(); // Profit for this line
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sales_order_id');
            $table->index('product_id');
            $table->index('inventory_batch_id');
            $table->index('product_sku');

            $table->foreign('sales_order_id')
                ->references('id')
                ->on('sales_orders')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('inventory_batch_id')
                ->references('id')
                ->on('inventory_batches')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_items');
    }
};
