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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Satu pesanan hanya boleh memiliki satu pembayaran.
            $table->foreignId('order_id')
                ->unique()
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('payment_number', 40)->unique();

            // cash, transfer, atau qris
            $table->string('payment_method', 20);

            // Total tagihan yang dilunasi
            $table->decimal('paid_amount', 15, 2);

            // Nominal uang yang diterima dari pelanggan
            $table->decimal('amount_received', 15, 2);

            // Kembalian, khusus pembayaran tunai
            $table->decimal('change_amount', 15, 2)->default(0);

            $table->timestamp('paid_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
