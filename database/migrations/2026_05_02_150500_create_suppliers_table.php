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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('tax_id', 50)->nullable();
            $table->string('website')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('credit_limit', 12, 2)->nullable();
            $table->integer('payment_terms')->default(0); // days
            $table->boolean('is_active')->default(true);
            $table->boolean('is_preferred')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('code');
            $table->index('name');
            $table->index('is_active');
            $table->index('is_preferred');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
