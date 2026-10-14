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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice', 50)->unique();
            // suppliers.id = INT UNSIGNED
            $table->unsignedInteger('supplier_id')->nullable();
            $table->date('date');
            $table->decimal('total', 15, 2)->default(0);
            $table->text('note')->nullable();
            // users.id kita gunakan BIGINT UNSIGNED
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('date');
            $table->index('supplier_id');
            // Foreign key supplier
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
