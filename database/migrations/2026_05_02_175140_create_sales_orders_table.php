<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->date('order_date');
            $table->time('order_time');
            $table->string('status', 20)->default('pending');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('change_amount', 10, 2)->default(0);
            $table->string('payment_status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'order_date']);
            $table->index('cashier_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
