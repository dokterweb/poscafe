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
        Schema::create('product_options', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('option_group_id');
            $table->string('name', 100);
            $table->decimal('price_adjustment', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('option_group_id')
                ->references('id')
                ->on('option_groups')
                ->cascadeOnDelete();

            $table->unique(['option_group_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_options');
    }
};
