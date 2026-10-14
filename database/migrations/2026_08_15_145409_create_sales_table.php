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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice', 50)->unique();
            $table->unsignedInteger('customer_id')->nullable();
            $table->enum('jenis', ['toko','online',])->default('toko');
            $table->decimal('total_hpp', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('final_price', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->date('date');
            $table->foreignId('user_id');
            $table->enum('payment_status', ['belum_lunas','lunas',])->default('belum_lunas');
            $table->timestamps();
            $table->index('date');
            $table->index('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
