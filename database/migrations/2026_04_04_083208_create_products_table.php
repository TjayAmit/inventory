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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('product_code', 50)->unique();
            $table->string('barcode', 13)->unique()->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_taxable')->default(true);
            $table->string('unit', 20)->default('pcs');
            $table->decimal('weight', 8, 3)->nullable(); // in kg
            $table->decimal('volume', 8, 3)->nullable(); // in liters
            $table->string('brand', 100)->nullable();
            $table->string('manufacturer', 100)->nullable();
            $table->string('supplier', 100)->nullable();
            $table->integer('reorder_point')->default(10);
            $table->integer('max_stock')->default(1000);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['name', 'is_active']);
            $table->index('barcode');
            $table->index('product_code');
            $table->index(['category_id', 'is_active']);
            $table->index(['price', 'is_active']);
            $table->index('brand');
            $table->index('supplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
