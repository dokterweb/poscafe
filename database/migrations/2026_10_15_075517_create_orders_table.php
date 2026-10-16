<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_number', 30)->unique();

            // dine_in atau takeaway
            $table->string('order_type', 20)->default('dine_in');

            // mejas.id menggunakan increments(), yaitu unsigned integer
            $table->unsignedInteger('meja_id')->nullable();

            // draft, open, paid, cancelled
            $table->string('status', 20)->default('open');

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('meja_id')
                ->references('id')
                ->on('mejas')
                ->nullOnDelete();

            $table->index(['status', 'order_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
