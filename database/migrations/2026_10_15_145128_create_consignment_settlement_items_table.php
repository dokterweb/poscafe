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
        Schema::create('consignment_settlement_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consignment_settlement_id')
                ->constrained('consignment_settlements')
                ->cascadeOnDelete();

            $table->foreignId('order_item_id')
                ->unique()
                ->constrained('order_items')
                ->restrictOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->string('product_name', 150);

            $table->unsignedInteger('quantity');

            $table->decimal('partner_unit_price', 15, 2);

            $table->decimal('total_amount', 15, 2);

            $table->timestamps();

            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_settlement_items');
    }
};
