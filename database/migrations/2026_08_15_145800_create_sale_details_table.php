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
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('qty');
            $table->decimal('discount', 8, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('total_hpp', 15, 2)->default(0);
            $table->timestamps();
            $table->index('sale_id');
            $table->index('product_id');
            $table->foreign('sale_id')->references('id')->on('sales')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_details');
    }
};
