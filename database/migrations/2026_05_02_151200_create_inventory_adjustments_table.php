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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number', 50);
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('inventory_batch_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->enum('adjustment_type', ['stock_in', 'stock_out', 'damage', 'loss', 'theft', 'count_correction', 'expiry'])->default('count_correction');
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected'])->default('draft');
            $table->integer('quantity_before')->default(0);
            $table->integer('quantity_adjusted')->default(0);
            $table->integer('quantity_after')->default(0);
            $table->decimal('unit_cost', 10, 4)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamp('adjustment_date');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('adjustment_number');
            $table->index('product_id');
            $table->index('branch_id');
            $table->index('inventory_batch_id');
            $table->index('created_by');
            $table->index('approved_by');
            $table->index('adjustment_type');
            $table->index('status');
            $table->index('adjustment_date');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('inventory_batch_id')
                ->references('id')
                ->on('inventory_batches')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
