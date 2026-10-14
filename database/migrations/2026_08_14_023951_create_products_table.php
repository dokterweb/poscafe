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
            $table->string('barcode', 50)->unique();
            $table->string('name', 150);
            $table->unsignedInteger('category_id')->nullable();
            $table->decimal('hpp', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('name');
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
