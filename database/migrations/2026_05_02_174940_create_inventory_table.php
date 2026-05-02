<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->integer('quantity_available')->default(0);
            $table->decimal('average_cost', 10, 4)->default(0);
            $table->date('last_count_date')->nullable();
            $table->date('last_received_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'branch_id']);
            $table->index('branch_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
