<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // products.id menggunakan $table->id()
            $table->unsignedBigInteger('product_id')->nullable();

            // product_variants.id menggunakan increments()
            $table->unsignedInteger('product_variant_id')->nullable();

            // Snapshot data produk pada saat dipesan
            $table->string('product_name', 150);
            $table->string('variant_name', 100)->nullable();

            // Simpan pilihan tambahan sebagai snapshot JSON
            $table->json('selected_options')->nullable();

            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->nullOnDelete();

            $table->foreign('product_variant_id')
                ->references('id')
                ->on('product_variants')
                ->nullOnDelete();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
