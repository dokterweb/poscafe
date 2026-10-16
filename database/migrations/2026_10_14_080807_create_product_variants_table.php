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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('product_id');
            $table->string('name', 100);
            $table->decimal('hpp', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->unique(['product_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
