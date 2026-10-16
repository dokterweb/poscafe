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
        Schema::create('product_option_group', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('option_group_id');

            $table->primary(['product_id', 'option_group_id']);

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('option_group_id')
                ->references('id')
                ->on('option_groups')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_option_group');
    }
};
