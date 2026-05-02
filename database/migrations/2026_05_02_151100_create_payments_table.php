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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 50);
            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('received_by');
            $table->enum('payment_method', ['cash', 'card', 'check', 'bank_transfer', 'mobile_money', 'credit', 'other'])->default('cash');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->decimal('refunded_amount', 12, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate', 10, 6)->default(1.000000);
            $table->string('card_type', 50)->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->string('authorization_code', 100)->nullable();
            $table->string('check_number', 50)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('payment_date');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('payment_number');
            $table->index('sales_order_id');
            $table->index('customer_id');
            $table->index('received_by');
            $table->index('payment_method');
            $table->index('status');
            $table->index('payment_date');

            $table->foreign('sales_order_id')
                ->references('id')
                ->on('sales_orders')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('received_by')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
