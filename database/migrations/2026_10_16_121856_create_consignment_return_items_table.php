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
        Schema::create('consignment_return_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consignment_return_id')
                ->constrained('consignment_returns')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->foreignId('order_item_id')
                ->nullable()
                ->constrained('order_items')
                ->restrictOnDelete();

            $table->foreignId('consignment_settlement_id')
                ->nullable()
                ->constrained('consignment_settlements')
                ->restrictOnDelete();

            $table->string('product_name', 150);

            $table->unsignedInteger('quantity');

            $table->decimal('partner_unit_price', 15, 2);

            $table->decimal('total_amount', 15, 2);

            $table->timestamps();

            $table->index('product_id');
            $table->index('order_item_id');
            $table->index('consignment_settlement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_return_items');
    }
};
