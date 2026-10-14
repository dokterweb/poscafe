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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
             // Produk
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            // Jenis pergerakan
            $table->enum('type', ['in','out',]);
            // Qty pergerakan
            $table->integer('qty');
            // Stok sebelum transaksi
            $table->integer('stock_before');
            // Stok setelah transaksi
            $table->integer('stock_after');
            // Sumber transaksi
            $table->string('reference_type')->nullable();
            // ID transaksi sumber
            $table->unsignedBigInteger('reference_id')->nullable();
            // Keterangan
            $table->text('note')->nullable();
            // User yang melakukan transaksi
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Index
            $table->index('product_id');
            $table->index('type');
            $table->index(['reference_type','reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
